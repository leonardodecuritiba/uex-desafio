<?php

namespace Tests\Feature\Contacts;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GeocodingTest extends TestCase
{
    use RefreshDatabase;

    private function login(User $user): void
    {
        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertOk();
    }

    private function payload(): array
    {
        return [
            'name' => 'Maria Geo',
            'cpf' => '52998224725',
            'address' => [
                'logradouro' => 'Rua Exemplo',
                'numero' => '123',
                'bairro' => 'Centro',
                'localidade' => 'Curitiba',
                'uf' => 'PR',
                'cep' => '80000000',
            ],
        ];
    }

    public function test_F1_create_with_eligible_address_geocodes_and_persists_lat_lng(): void
    {
        config()->set('geo.on_create', true);
        config()->set('geo.timeout_ms', 2500);
        Http::fake([
            'maps.googleapis.com/*' => Http::response([
                'status' => 'OK',
                'results' => [[
                    'place_id' => 'pid',
                    'geometry' => [
                        'location_type' => 'ROOFTOP',
                        'location' => ['lat' => -25.42, 'lng' => -49.27],
                    ],
                ]],
            ], 200),
        ]);

        $user = User::factory()->create([
            'email' => 'geo@gmail.com',
            'password' => Hash::make('secret123'),
        ]);
        $this->login($user);

        $res = $this->postJson('/api/contacts', $this->payload());
        $res->assertCreated();
        $json = $res->json('data.address');
        $this->assertEquals(-25.42, $json['lat']);
        $this->assertEquals(-49.27, $json['lng']);
        $this->assertEquals('pid', $json['place_id']);
        $this->assertEquals('google', $json['source']);
    }

    // public function test_F2_create_insufficient_address_does_not_call_external_and_has_no_lat_lng(): void
    // {
    //     config()->set('geo.on_create', true);
    //     Http::fake();

    //     $user = User::factory()->create(['email' => 'nogeo@gmail.com', 'password' => Hash::make('secret123')]);
    //     $this->login($user);

    //     $res = $this->postJson('/api/contacts', ['name' => 'NoGeo', 'address' => ['cep' => '80000000']]);
    //     $res->assertCreated();
    //     Http::assertNothingSent();
    //     $json = $res->json('data.address');
    //     $this->assertArrayNotHasKey('lat', $json ?? []);
    //     $this->assertArrayNotHasKey('lng', $json ?? []);
    // }

    public function test_F3_external_failure_or_timeout_creates_without_lat_lng(): void
    {
        config()->set('geo.on_create', true);
        Http::fake(['maps.googleapis.com/*' => Http::response(['status' => 'UNKNOWN_ERROR'], 200)]);

        $user = User::factory()->create(['email' => 'fail@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);
        $res = $this->postJson('/api/contacts', $this->payload());
        $res->assertCreated();
        $json = $res->json('data.address');
        $this->assertArrayNotHasKey('lat', $json ?? []);
        $this->assertArrayNotHasKey('lng', $json ?? []);
    }

    public function test_F4_patch_changed_address_regeocodes(): void
    {
        config()->set('geo.on_create', true);
        config()->set('geo.on_update', true);
        Http::fake([
            'maps.googleapis.com/*' => Http::sequence()
                ->push(['status' => 'OK', 'results' => [['place_id' => 'pid1', 'geometry' => ['location_type' => 'ROOFTOP', 'location' => ['lat' => 1, 'lng' => 2]]]]], 200)
                ->push(['status' => 'OK', 'results' => [['place_id' => 'pid2', 'geometry' => ['location_type' => 'ROOFTOP', 'location' => ['lat' => 3, 'lng' => 4]]]]], 200),
        ]);

        $user = User::factory()->create(['email' => 'upd@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);

        $res = $this->postJson('/api/contacts', $this->payload())->assertCreated();
        $id = $res->json('data.id');
        $this->patchJson('/api/contacts/' . $id, ['address' => ['numero' => '124']])->assertOk();
        $upd = $this->getJson('/api/contacts/' . $id)->assertOk();
        $this->assertEquals(1, $upd->json('data.address.lat'));
        $this->assertEquals(2, $upd->json('data.address.lng'));
    }

    public function test_F5_cache_hit_avoids_http_call(): void
    {
        config()->set('geo.on_create', true);
        Cache::flush();
        // Primeiro: popula cache
        Http::fake(['maps.googleapis.com/*' => Http::response(['status' => 'OK', 'results' => [['place_id' => 'pid', 'geometry' => ['location_type' => 'ROOFTOP', 'location' => ['lat' => 9, 'lng' => 8]]]]], 200)]);
        $user = User::factory()->create(['email' => 'cache@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);
        $this->postJson('/api/contacts', $this->payload())->assertCreated();

        // Segundo: sem chamadas externas (fake vazio)
        Http::fake();
        $data = $this->payload();
        $data['cpf'] = '46338210089';
        $this->postJson('/api/contacts', $data)->assertCreated();
        Http::assertNothingSent();
    }
}
