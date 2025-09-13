<?php

namespace Tests\Feature\Contacts;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DeleteContactTest extends TestCase
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

    public function test_owner_deletes_contact_returns_204(): void
    {
        $user = User::factory()->create(['email' => 'owner@del.com', 'password' => Hash::make('secret123')]);
        $this->login($user);
        $contact = Contact::factory()->create(['user_id' => $user->id, 'cpf' => '52998224725', 'address' => $this->validAddress()]);

        $this->deleteJson('/api/contacts/' . $contact->id)->assertNoContent();

        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);

        // CPF deve estar liberado para reuso pelo mesmo usuário
        $this->postJson('/api/contacts', [
            'name' => 'Novo',
            'cpf' => '52998224725',
            'address' => $this->validAddress()
        ])->assertCreated();
    }

    public function test_other_user_cannot_delete_returns_403(): void
    {
        $owner = User::factory()->create(['email' => 'owner2@del.com', 'password' => Hash::make('secret123')]);
        $other = User::factory()->create(['email' => 'other@del.com', 'password' => Hash::make('secret123')]);
        $this->login($other);
        $contact = Contact::factory()->create(['user_id' => $owner->id, 'address' => $this->validAddress()]);

        $this->deleteJson('/api/contacts/' . $contact->id)->assertStatus(403);
    }

    public function test_unauthenticated_returns_401(): void
    {
        $contact = Contact::factory()->create();
        $this->deleteJson('/api/contacts/' . $contact->id)->assertStatus(401);
    }

    public function test_not_found_returns_404(): void
    {
        $user = User::factory()->create(['email' => 'nf@del.com', 'password' => Hash::make('secret123')]);
        $this->login($user);
        $this->deleteJson('/api/contacts/999999')->assertStatus(404);
    }
}
