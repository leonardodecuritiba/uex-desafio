<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_registers_with_valid_payload(): void
    {
        $payload = [
            'name' => 'Maria Silva',
            'email' => 'maria@gmail.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $res = $this->postJson('/api/auth/register', $payload);
        $res->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'name', 'email']])
            ->assertJsonMissingPath('data.password');

        $this->assertDatabaseHas('users', [
            'email' => 'maria@gmail.com',
            'name' => 'Maria Silva',
        ]);
    }

    public function test_duplicate_email_returns_422(): void
    {
        User::factory()->create(['email' => 'dup@gmail.com']);

        $res = $this->postJson('/api/auth/register', [
            'name' => 'Dup',
            'email' => 'dup@gmail.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $res->assertStatus(422)
            ->assertJsonStructure(['errors' => [['field', 'message']]]);
    }

    public function test_password_too_short_returns_422(): void
    {
        $res = $this->postJson('/api/auth/register', [
            'name' => 'Joao',
            'email' => 'joao@gmail.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $res->assertStatus(422)
            ->assertJsonStructure(['errors' => []]);
    }

    public function test_invalid_email_returns_422(): void
    {
        $res = $this->postJson('/api/auth/register', [
            'name' => 'Ana',
            'email' => 'not-an-email',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $res->assertStatus(422)
            ->assertJsonStructure(['errors' => []]);
    }
}
