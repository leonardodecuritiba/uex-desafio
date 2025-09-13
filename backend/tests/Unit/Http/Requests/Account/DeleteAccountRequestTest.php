<?php

namespace Tests\Unit\Http\Requests\Account;

use App\Infrastructure\Http\Requests\Account\DeleteAccountRequest;
use PHPUnit\Framework\TestCase;

class DeleteAccountRequestTest extends TestCase
{
    public function test_rules_include_current_password_rule(): void
    {
        $req = new DeleteAccountRequest();
        $rules = $req->rules();

        $this->assertArrayHasKey('password', $rules);
        $passwordRules = $rules['password'];
        $this->assertIsArray($passwordRules);

        $found = false;
        foreach ($passwordRules as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'current_password')) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'A regra current_password deve estar presente.');
    }
}

