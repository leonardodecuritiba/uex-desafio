<?php

namespace Tests\Unit\Infrastructure\Http\Requests\Auth;

use App\Infrastructure\Http\Requests\Auth\ForgotPasswordRequest;
use PHPUnit\Framework\TestCase;

class ForgotPasswordRequestTest extends TestCase
{
    public function test_rules_contain_required_email_with_rfc_dns(): void
    {
        $req = new ForgotPasswordRequest();
        $rules = $req->rules();

        $this->assertArrayHasKey('email', $rules);
        $this->assertContains('required', $rules['email']);
        $this->assertTrue(collect($rules['email'])->contains(fn ($r) => str_starts_with($r, 'email')));
        $this->assertTrue(collect($rules['email'])->contains('email:rfc,dns'));
    }
}

