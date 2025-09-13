<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_existing_email_returns_200_and_sends_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'john@gmail.com']);

        $res = $this->postJson('/api/auth/forgot-password', [
            'email' => 'john@gmail.com',
        ]);

        $res->assertOk()
            ->assertJson(['message' => 'Se o e-mail existir, enviaremos instruções.']);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_forgot_password_unknown_email_returns_200_and_sends_nothing(): void
    {
        Notification::fake();

        $res = $this->postJson('/api/auth/forgot-password', [
            'email' => 'unknown@gmail.com',
        ]);

        $res->assertOk()
            ->assertJson(['message' => 'Se o e-mail existir, enviaremos instruções.']);

        Notification::assertNothingSent();
    }
}
