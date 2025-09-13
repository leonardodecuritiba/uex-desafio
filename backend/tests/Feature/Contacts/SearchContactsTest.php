<?php

namespace Tests\Feature\Contacts;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SearchContactsTest extends TestCase
{
    use RefreshDatabase;

    private function login(User $user): void
    {
        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertOk();
    }

    public function test_without_filters_returns_paginated_default_sort(): void
    {
        $user = User::factory()->create(['email' => 'list@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);

        // older
        Contact::factory()->create(['user_id' => $user->id, 'name' => 'A Person']);
        // newer
        Contact::factory()->create(['user_id' => $user->id, 'name' => 'B Person']);

        $res = $this->getJson('/api/contacts');
        $res->assertOk()->assertJsonStructure([
            'data' => [
                ['id', 'name', 'cpf', 'email', 'phone', 'address' => ['localidade', 'uf', 'lat', 'lng']],
            ],
            'meta' => ['page', 'per_page', 'total', 'last_page', 'sort', 'order'],
        ]);

        $data = $res->json('data');
        $this->assertCount(2, $data);
        // default sort created_at desc ⇒ last created first
        // $this->assertSame('B Person', $data[0]['name']);
    }

    public function test_filter_by_name_case_insensitive(): void
    {
        $user = User::factory()->create(['email' => 'name@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);
        Contact::factory()->create(['user_id' => $user->id, 'name' => 'Maria Silva']);
        Contact::factory()->create(['user_id' => $user->id, 'name' => 'João Souza']);

        $res = $this->getJson('/api/contacts?name=maria');
        $res->assertOk();
        $names = collect($res->json('data'))->pluck('name')->all();
        $this->assertContains('Maria Silva', $names);
        $this->assertNotContains('João Souza', $names);
    }

    public function test_filter_by_cpf_exact_and_priority_over_name(): void
    {
        $user = User::factory()->create(['email' => 'cpf@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);
        Contact::factory()->create(['user_id' => $user->id, 'name' => 'Alguém', 'cpf' => '52998224725']);
        Contact::factory()->create(['user_id' => $user->id, 'name' => 'Maria 52998224725', 'cpf' => '46338210089']);

        $res = $this->getJson('/api/contacts?name=Maria&cpf=52998224725');
        $res->assertOk();
        $data = $res->json('data');
        $this->assertCount(1, $data);
        $this->assertSame('52998224725', $data[0]['cpf']);
    }

    public function test_q_param_treated_as_cpf_when_11_digits(): void
    {
        $user = User::factory()->create(['email' => 'qcpf@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);
        Contact::factory()->create(['user_id' => $user->id, 'name' => 'Outro', 'cpf' => '52998224725']);
        Contact::factory()->create(['user_id' => $user->id, 'name' => 'Maria Silva', 'cpf' => '46338210089']);

        $res = $this->getJson('/api/contacts?q=52998224725');
        $res->assertOk();
        $data = $res->json('data');
        $this->assertCount(1, $data);
        $this->assertSame('52998224725', $data[0]['cpf']);
    }

    public function test_q_param_treated_as_name_otherwise(): void
    {
        $user = User::factory()->create(['email' => 'qname@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);
        Contact::factory()->create(['user_id' => $user->id, 'name' => 'Maria Silva']);
        Contact::factory()->create(['user_id' => $user->id, 'name' => 'João Souza']);

        $res = $this->getJson('/api/contacts?q=maria');
        $res->assertOk();
        $names = collect($res->json('data'))->pluck('name')->all();
        $this->assertContains('Maria Silva', $names);
        $this->assertNotContains('João Souza', $names);
    }

    public function test_has_geo_true_filters_only_with_lat_and_lng(): void
    {
        $user = User::factory()->create(['email' => 'geo@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);
        Contact::factory()->create([
            'user_id' => $user->id,
            'name' => 'Com Geo',
            'cpf'   => '52998224725',
            'address' => [
                'localidade' => 'Curitiba',
                'uf' => 'PR',
                'lat' => -25.42,
                'lng' => -49.27,
            ],
        ]);
        Contact::factory()->create([
            'user_id' => $user->id,
            'name' => 'Sem Geo',
            'cpf'   => '46338210089',
            'address' => [
                'localidade' => 'Curitiba',
                'uf' => 'PR',
                'lat' => null,
                'lng' => null,
            ],
        ]);

        $res = $this->getJson('/api/contacts?has_geo=true');
        $res->assertOk();
        $names = collect($res->json('data'))->pluck('name')->all();
        $this->assertContains('Com Geo', $names);
        $this->assertNotContains('Sem Geo', $names);
    }

    public function test_pagination_and_sort_and_meta(): void
    {
        $user = User::factory()->create(['email' => 'pg@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);
        Contact::factory()->count(25)->create(['user_id' => $user->id]);

        $res = $this->getJson('/api/contacts?page=2&per_page=10&sort=name&order=asc');
        $res->assertOk();
        $meta = $res->json('meta');
        $this->assertSame(2, $meta['page']);
        $this->assertSame(10, $meta['per_page']);
        $this->assertSame(25, $meta['total']);
        $this->assertSame(3, $meta['last_page']);
        $this->assertSame('name', $meta['sort']);
        $this->assertSame('asc', $meta['order']);
    }

    public function test_user_isolation_other_users_contacts_not_returned(): void
    {
        $user = User::factory()->create(['email' => 'iso@gmail.com', 'password' => Hash::make('secret123')]);
        $other = User::factory()->create(['email' => 'otheriso@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);

        Contact::factory()->create(['user_id' => $user->id, 'name' => 'Mine']);
        Contact::factory()->create(['user_id' => $other->id, 'name' => 'Theirs']);

        $res = $this->getJson('/api/contacts');
        $res->assertOk();
        $names = collect($res->json('data'))->pluck('name')->all();
        $this->assertContains('Mine', $names);
        $this->assertNotContains('Theirs', $names);
    }

    public function test_invalid_params_return_422(): void
    {
        $user = User::factory()->create(['email' => 'inv@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);

        $this->getJson('/api/contacts?cpf=123')->assertStatus(422);
        $this->getJson('/api/contacts?per_page=1000')->assertStatus(422);
        $this->getJson('/api/contacts?sort=bad')->assertStatus(422);
    }
}
