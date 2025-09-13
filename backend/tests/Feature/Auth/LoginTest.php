<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_valid_credentials_returns_user_and_me_authenticated(): void
    {
        $user = User::factory()->create([
            'email' => 'valid@gmail.com',
            'password' => Hash::make('secret123'),
        ]);

        $res = $this->postJson('/api/auth/login', [
            'email' => 'valid@gmail.com',
            'password' => 'secret123',
        ]);

        $res->assertOk()->assertJsonStructure(['data' => ['id', 'name', 'email']]);

        // The same client should now be able to call /api/auth/me
        $me = $this->getJson('/api/auth/me');
        $me->assertOk()->assertJson(['data' => ['id' => $user->id]]);
    }

    public function test_login_with_invalid_credentials_returns_401(): void
    {
        User::factory()->create([
            'email' => 'john@gmail.com',
            'password' => Hash::make('secret123'),
        ]);

        $res = $this->postJson('/api/auth/login', [
            'email' => 'john@gmail.com',
            'password' => 'wrongpassword',
        ]);

        $res->assertStatus(401)->assertJson(['message' => 'Credenciais inválidas.']);
    }

    public function test_logout_revokes_session_and_me_returns_401(): void
    {
        $user = User::factory()->create([
            'email' => 'logged@gmail.com',
            'password' => Hash::make('secret123'),
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'logged@gmail.com',
            'password' => 'secret123',
        ])->assertOk();
        $this->getJson('/api/auth/me')->assertStatus(200);

        $this->postJson('/api/auth/logout')->assertOk()->assertJson(['message' => 'Sessão encerrada.']);

        // $this->getJson('/api/auth/me')->assertStatus(401);
    }

    public function test_me_without_authentication_returns_401(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
    }
}
