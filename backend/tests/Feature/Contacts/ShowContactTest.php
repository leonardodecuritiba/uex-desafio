<?php

namespace Tests\Feature\Contacts;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ShowContactTest extends TestCase
{
    use RefreshDatabase;

    private function login(User $user): void
    {
        // Autentica via sessão diretamente para evitar dependência de validação DNS de e-mail
        $this->actingAs($user);
    }

    public function test_authenticated_user_can_view_own_contact(): void
    {
        $user = User::factory()->create(['email' => 'show1@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);

        $contact = Contact::factory()->create([
            'user_id' => $user->id,
            'name' => 'Maria Silva',
            'cpf' => '52998224725',
            'address' => [
                'cep' => '80000000',
                'logradouro' => 'Rua Exemplo',
                'numero' => '123',
                'complemento' => null,
                'bairro' => 'Centro',
                'localidade' => 'Curitiba',
                'uf' => 'PR',
                'lat' => -25.4284,
                'lng' => -49.2733,
            ],
        ]);

        $res = $this->getJson('/api/contacts/' . $contact->id);
        $res->assertOk()->assertJsonStructure([
            'data' => [
                'id', 'name', 'cpf', 'email', 'phone',
                'address' => ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'localidade', 'uf', 'lat', 'lng'],
                'created_at', 'updated_at',
            ],
        ]);

        $this->assertSame('Maria Silva', $res->json('data.name'));
        $this->assertSame('52998224725', $res->json('data.cpf'));
        $this->assertSame('Curitiba', $res->json('data.address.localidade'));
        $this->assertSame('PR', $res->json('data.address.uf'));
    }

    public function test_authenticated_user_cannot_view_others_contact_returns_404(): void
    {
        $user = User::factory()->create(['email' => 'show2@gmail.com', 'password' => Hash::make('secret123')]);
        $other = User::factory()->create(['email' => 'show2o@gmail.com', 'password' => Hash::make('secret123')]);
        $this->login($user);

        $othersContact = Contact::factory()->create(['user_id' => $other->id]);

        $this->getJson('/api/contacts/' . $othersContact->id)->assertStatus(404);
    }

    public function test_unauthenticated_returns_401(): void
    {
        $contact = Contact::factory()->create();
        $this->getJson('/api/contacts/' . $contact->id)->assertStatus(401);
    }
}
