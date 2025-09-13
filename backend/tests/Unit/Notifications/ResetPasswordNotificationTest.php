<?php

namespace Tests\Unit\Notifications;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

class ResetPasswordNotificationTest extends TestCase
{
    public function test_formats_link_with_frontend_url_and_query_params(): void
    {
        $user = new User();
        $user->email = 'john@gmail.com';
        $token = 'abc123';

        $_SERVER['FRONTEND_URL'] = 'http://frontend.local';

        $n = new ResetPasswordNotification($token);
        $mail = $n->toMail($user);

        $this->assertSame('http://frontend.local/reset-password?token=abc123&email=john%40gmail.com', $mail->actionUrl);
        $this->assertSame('Redefinir senha', $mail->actionText);
    }
}
