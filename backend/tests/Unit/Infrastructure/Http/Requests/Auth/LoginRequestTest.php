<?php

namespace Tests\Unit\Infrastructure\Http\Requests\Auth;

use App\Infrastructure\Http\Requests\Auth\LoginRequest;
use PHPUnit\Framework\TestCase;

class LoginRequestTest extends TestCase
{
    public function test_rules_require_email_and_password_constraints(): void
    {
        $req = new LoginRequest();
        $rules = $req->rules();

        $this->assertArrayHasKey('email', $rules);
        $this->assertTrue(in_array('required', $rules['email'], true));
        $this->assertTrue(in_array('email:rfc,dns', $rules['email'], true));

        $this->assertArrayHasKey('password', $rules);
        $this->assertTrue(in_array('required', $rules['password'], true));
        $this->assertTrue(in_array('string', $rules['password'], true));
        $this->assertTrue(in_array('min:8', $rules['password'], true));
    }
}

