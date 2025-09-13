<?php

namespace Tests\Feature\Contacts;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ValidationRulesTest extends TestCase
{
    use RefreshDatabase;

    private function login(User $user): void
    {
        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertOk();
    }

    private function validAddress(): array
    {
        return [
            'cep' => '80000000',
            'logradouro' => 'Rua A',
            'numero' => '123',
            'bairro' => 'Centro',
            'localidade' => 'Curitiba',
            'uf' => 'PR',
        ];
    }

    public function test_F2_post_without_cpf_returns_422(): void
    {
        $user = User::factory()->create(['email' => 'v1@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);
        $res = $this->postJson('/api/contacts', ['name' => 'Ana', 'address' => $this->validAddress()]);
        $res->assertStatus(422)->assertJsonStructure(['errors']);
    }

    public function test_F4_post_without_address_uf_returns_422(): void
    {
        $user = User::factory()->create(['email' => 'v2@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);
        $addr = $this->validAddress();
        unset($addr['uf']);
        $res = $this->postJson('/api/contacts', ['name' => 'Ana', 'cpf' => '52998224725', 'address' => $addr]);
        $res->assertStatus(422);
    }

    public function test_F5_patch_remove_complemento_allowed(): void
    {
        $user = User::factory()->create(['email' => 'v3@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);
        $payload = ['name' => 'Ana', 'cpf' => '52998224725', 'address' => $this->validAddress() + ['complemento' => 'Ap 1']];
        $id = $this->postJson('/api/contacts', $payload)->assertCreated()->json('data.id');
        $this->patchJson('/api/contacts/' . $id, ['address' => ['complemento' => null]])->assertOk();
    }

    public function test_F6_patch_remove_cpf_should_422(): void
    {
        $user = User::factory()->create(['email' => 'v4@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);
        $id = $this->postJson('/api/contacts', ['name' => 'Ana', 'cpf' => '52998224725', 'address' => $this->validAddress()])->json('data.id');
        $this->patchJson('/api/contacts/' . $id, ['cpf' => null])->assertStatus(422);
    }

    public function test_F7_patch_remove_address_uf_should_422(): void
    {
        $user = User::factory()->create(['email' => 'v5@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);
        $id = $this->postJson('/api/contacts', ['name' => 'Ana', 'cpf' => '52998224725', 'address' => $this->validAddress()])->json('data.id');
        $this->patchJson('/api/contacts/' . $id, ['address' => ['uf' => null]])->assertStatus(422);
    }
}
