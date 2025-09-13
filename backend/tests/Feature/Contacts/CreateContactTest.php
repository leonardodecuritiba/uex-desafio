<?php

namespace Tests\Feature\Contacts;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateContactTest extends TestCase
{
    use RefreshDatabase;

    private function login(User $user): void
    {
        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertOk();
    }

    public function test_create_contact_full_payload_returns_201(): void
    {
        $user = User::factory()->create([
            'email' => 'full@gmail.com',
            'password' => Hash::make('secret123'),
        ]);
        $this->login($user);

        $payload = [
            'name' => 'Maria Silva',
            'cpf' => '52998224725',
            'email' => 'maria@gmail.com',
            'phone' => '41999990000',
            'address' => [
                'cep' => '80000000',
                'logradouro' => 'Rua Exemplo',
                'numero' => '123',
                'complemento' => 'Ap 12',
                'bairro' => 'Centro',
                'localidade' => 'Curitiba',
                'uf' => 'PR',
            ],
        ];

        $res = $this->postJson('/api/contacts', $payload);
        $res->assertCreated()->assertJsonStructure(['data' => ['id', 'name', 'cpf', 'email', 'phone', 'address' => ['cep', 'logradouro', 'uf'], 'created_at']]);

        $this->assertDatabaseHas('contacts', [
            'user_id' => $user->id,
            'name' => 'Maria Silva',
            'cpf' => '52998224725',
        ]);
    }

    public function test_minimal_valid_payload_returns_201(): void
    {
        $user = User::factory()->create([
            'email' => 'min@gmail.com',
            'password' => Hash::make('secret123'),
        ]);
        $this->login($user);

        $res = $this->postJson('/api/contacts', [
            'name' => 'João',
            'cpf' => '52998224725',
            'address' => [
                'cep' => '80000000',
                'logradouro' => 'Rua A',
                'numero' => '1',
                'bairro' => 'Centro',
                'localidade' => 'Curitiba',
                'uf' => 'PR',
            ],
        ]);

        $res->assertCreated();
        $this->assertDatabaseHas('contacts', [
            'user_id' => $user->id,
            'name' => 'João',
        ]);
    }

    public function test_invalid_cpf_returns_422(): void
    {
        $user = User::factory()->create([
            'email' => 'cpf@gmail.com',
            'password' => Hash::make('secret123'),
        ]);
        $this->login($user);

        $res = $this->postJson('/api/contacts', [
            'name' => 'Ana',
            'cpf' => '12345678900', // inválido
        ]);

        $res->assertStatus(422)->assertJsonStructure(['errors' => []]);
    }

    public function test_duplicate_cpf_for_same_user_returns_422(): void
    {
        $user = User::factory()->create([
            'email' => 'dup@gmail.com',
            'password' => Hash::make('secret123'),
        ]);
        $this->login($user);

        $payload = [
            'name' => 'Primeiro',
            'cpf' => '52998224725',
            'address' => [
                'cep' => '80000000',
                'logradouro' => 'Rua A',
                'numero' => '1',
                'bairro' => 'Centro',
                'localidade' => 'Curitiba',
                'uf' => 'PR',
            ],
        ];
        $this->postJson('/api/contacts', $payload)->assertCreated();

        $res = $this->postJson('/api/contacts', [
            'name' => 'Segundo',
            'cpf' => '52998224725',
            'address' => [
                'cep' => '80000000',
                'logradouro' => 'Rua A',
                'numero' => '1',
                'bairro' => 'Centro',
                'localidade' => 'Curitiba',
                'uf' => 'PR',
            ],
        ]);

        $res->assertStatus(422);
    }

    public function test_unauthenticated_returns_401(): void
    {
        $this->postJson('/api/contacts', [
            'name' => 'SemAuth',
        ])->assertStatus(401);
    }
}
