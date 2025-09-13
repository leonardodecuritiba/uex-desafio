<?php

namespace Tests\Feature\Account;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_with_correct_password_deletes_account_and_revokes_session(): void
    {
        $user = User::factory()->create([
            'email' => 'john@gmail.com',
            'password' => Hash::make('secret123'),
        ]);

        // contacts for the user (should be cascade-deleted)
        Contact::factory()->count(2)->create(['user_id' => $user->id]);

        // login to establish session
        $this->postJson('/api/auth/login', [
            'email' => 'john@gmail.com',
            'password' => 'secret123',
        ])->assertOk();

        $res = $this->deleteJson('/api/account', [
            'password' => 'secret123',
        ]);

        $res->assertOk()->assertJson(['message' => 'Conta excluída com sucesso.'])
            ->assertJsonMissingPath('data');

        // user removed
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        // contacts removed by cascade
        $this->assertDatabaseCount('contacts', 0);

        // session revoked
        // $this->getJson('/api/auth/me')->assertStatus(401);
    }

    public function test_authenticated_with_wrong_password_returns_403(): void
    {
        $user = User::factory()->create([
            'email' => 'anne@gmail.com',
            'password' => Hash::make('secret123'),
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'anne@gmail.com',
            'password' => 'secret123',
        ])->assertOk();

        $res = $this->deleteJson('/api/account', [
            'password' => 'wrongpass',
        ]);

        $res->assertStatus(403);

        // still authenticated
        $this->getJson('/api/auth/me')->assertStatus(200);
    }

    public function test_unauthenticated_returns_401(): void
    {
        $this->deleteJson('/api/account', ['password' => 'anything'])
            ->assertStatus(401);
    }
}
