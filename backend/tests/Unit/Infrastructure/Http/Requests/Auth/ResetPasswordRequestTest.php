<?php

namespace Tests\Unit\Infrastructure\Http\Requests\Auth;

use App\Infrastructure\Http\Requests\Auth\ResetPasswordRequest;
use PHPUnit\Framework\TestCase;

class ResetPasswordRequestTest extends TestCase
{
    public function test_rules_require_token_email_and_confirmed_password_min_8(): void
    {
        $req = new ResetPasswordRequest();
        $rules = $req->rules();

        $this->assertArrayHasKey('token', $rules);
        $this->assertContains('required', $rules['token']);

        $this->assertArrayHasKey('email', $rules);
        $this->assertContains('required', $rules['email']);
        $this->assertTrue(collect($rules['email'])->contains('email:rfc,dns'));

        $this->assertArrayHasKey('password', $rules);
        $this->assertTrue(collect($rules['password'])->contains('required'));
        $this->assertTrue(collect($rules['password'])->contains('min:8'));
        $this->assertTrue(collect($rules['password'])->contains('confirmed'));
    }
}

