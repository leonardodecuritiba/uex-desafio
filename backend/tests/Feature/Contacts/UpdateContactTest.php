<?php

namespace Tests\Feature\Contacts;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UpdateContactTest extends TestCase
{
    use RefreshDatabase;

    private function login(User $user): void
    {
        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertOk();
    }

    public function test_owner_updates_name(): void
    {
        $user = User::factory()->create(['email' => 'owner@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);
        $contact = Contact::factory()->create(['user_id' => $user->id, 'name' => 'Old Name']);

        $res = $this->patchJson('/api/contacts/' . $contact->id, [
            'name' => 'New Name',
        ]);

        $res->assertOk()->assertJson(['data' => ['name' => 'New Name']]);
        $this->assertDatabaseHas('contacts', ['id' => $contact->id, 'name' => 'New Name']);
    }

    public function test_owner_merges_address_and_remove_field_with_null(): void
    {
        $user = User::factory()->create(['email' => 'addr@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);
        $contact = Contact::factory()->create([
            'user_id' => $user->id,
            'address' => [
                'cep' => '80000000',
                'logradouro' => 'Rua A',
                'numero' => '123',
                'complemento' => 'Ap 1',
                'bairro' => 'Centro',
                'localidade' => 'Curitiba',
                'uf' => 'PR',
            ],
        ]);

        $res = $this->patchJson('/api/contacts/' . $contact->id, [
            'address' => [
                'numero' => '456',
                'complemento' => null,
            ],
        ]);

        $res->assertOk();
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'user_id' => $user->id,
        ]);
        $updated = Contact::find($contact->id);
        $this->assertEquals('456', $updated->address['numero']);
        $this->assertArrayNotHasKey('complemento', $updated->address);
        $this->assertEquals('PR', $updated->address['uf']);
    }

    public function test_invalid_cpf_returns_422(): void
    {
        $user = User::factory()->create(['email' => 'cpf@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);
        $contact = Contact::factory()->create(['user_id' => $user->id]);

        $this->patchJson('/api/contacts/' . $contact->id, [
            'cpf' => '12345678900',
        ])->assertStatus(422);
    }

    public function test_duplicate_cpf_same_user_returns_422(): void
    {
        $user = User::factory()->create(['email' => 'dup@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);
        $c1 = Contact::factory()->create(['user_id' => $user->id, 'cpf' => '52998224725']);
        $c2 = Contact::factory()->create(['user_id' => $user->id, 'cpf' => '46338210089']);

        $this->patchJson('/api/contacts/' . $c2->id, [
            'cpf' => '52998224725',
        ])->assertStatus(422);
    }

    public function test_other_user_cannot_update_returns_403(): void
    {
        $owner = User::factory()->create(['email' => 'owner2@gmail.com', 'password' => Hash::make('secret123')]);
        $other = User::factory()->create(['email' => 'other@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($other);
        $contact = Contact::factory()->create(['user_id' => $owner->id]);

        $this->patchJson('/api/contacts/' . $contact->id, [
            'name' => 'XUSERSS',
        ])->assertStatus(403);
    }

    public function test_version_conflict_returns_409(): void
    {
        $user = User::factory()->create(['email' => 'ver@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);
        $contact = Contact::factory()->create(['user_id' => $user->id]);

        $this->patchJson('/api/contacts/' . $contact->id, [
            'name' => 'XUSERSS',
            'version' => '2000-01-01T00:00:00Z',
        ])->assertStatus(409);
    }

    public function test_not_found_returns_404(): void
    {
        $user = User::factory()->create(['email' => 'nf@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);
        $this->patchJson('/api/contacts/9999', ['name' => 'XXUSERSS'])->assertStatus(404);
    }
}
