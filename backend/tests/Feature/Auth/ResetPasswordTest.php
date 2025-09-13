<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_with_valid_token_updates_password(): void
    {
        $user = User::factory()->create(['email' => 'maria@gmail.com']);
        $token = Password::broker()->createToken($user);

        $res = $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => 'maria@gmail.com',
            'password' => 'newsecret123',
            'password_confirmation' => 'newsecret123',
        ]);

        $res->assertOk()->assertJson(['message' => 'Senha alterada com sucesso.']);

        $this->assertTrue(Hash::check('newsecret123', $user->fresh()->password));
    }

    public function test_reset_password_with_invalid_token_returns_422(): void
    {
        User::factory()->create(['email' => 'maria@gmail.com']);

        $res = $this->postJson('/api/auth/reset-password', [
            'token' => 'invalid-token',
            'email' => 'maria@gmail.com',
            'password' => 'newsecret123',
            'password_confirmation' => 'newsecret123',
        ]);

        $res->assertStatus(422)->assertJsonStructure(['errors' => []]);
    }

    public function test_reset_password_with_weak_password_returns_422(): void
    {
        User::factory()->create(['email' => 'maria@gmail.com']);

        $res = $this->postJson('/api/auth/reset-password', [
            'token' => 'anything',
            'email' => 'maria@gmail.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $res->assertStatus(422)->assertJsonStructure(['errors' => []]);
    }
}
