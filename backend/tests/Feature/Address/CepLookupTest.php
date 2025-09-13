<?php

namespace Tests\Feature\Address;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CepLookupTest extends TestCase
{
    use RefreshDatabase;

    private function login(User $user): void
    {
        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertOk();
    }

    public function test_lookup_existing_cep_returns_200_and_caches(): void
    {
        $user = User::factory()->create([
            'email' => 'cep@gmail.com',
            'password' => Hash::make('secret123'),
        ]);
        $this->login($user);

        Http::fake([
            '*/ws/80000000/json/*' => Http::response([
                'cep' => '80000000',
                'logradouro' => 'Rua Exemplo',
                'bairro' => 'Centro',
                'localidade' => 'Curitiba',
                'uf' => 'PR',
            ], 200),
        ]);

        $this->getJson('/api/address/cep/80000-000')->assertOk()
            ->assertJson(['data' => ['cep' => '80000000', 'uf' => 'PR']]);

        // segunda chamada (hit de cache): manter 200
        $this->getJson('/api/address/cep/80000000')->assertOk();
    }

    public function test_lookup_unknown_cep_returns_404(): void
    {
        $user = User::factory()->create([
            'email' => 'cep404@gmail.com',
            'password' => Hash::make('secret123'),
        ]);
        $this->login($user);

        Http::fake([
            '*/ws/00000000/json/*' => Http::response(['erro' => true], 200),
        ]);

        $this->getJson('/api/address/cep/00000000')->assertStatus(404);
    }
}
