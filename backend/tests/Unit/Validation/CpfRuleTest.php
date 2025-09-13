<?php

namespace Tests\Unit\Validation;

use App\Infrastructure\Validation\Rules\Cpf;
use PHPUnit\Framework\TestCase;

class CpfRuleTest extends TestCase
{
    public function test_valid_cpfs_pass(): void
    {
        $rule = new Cpf();
        $this->assertTrue($this->validate($rule, '52998224725'));
        $this->assertTrue($this->validate($rule, '13424131300'));
    }

    public function test_invalid_cpfs_fail(): void
    {
        $rule = new Cpf();
        $this->assertFalse($this->validate($rule, '12345678900'));
        $this->assertFalse($this->validate($rule, '00000000000'));
        $this->assertFalse($this->validate($rule, '11111111111'));
        $this->assertFalse($this->validate($rule, '5299822472')); // tamanho incorreto
    }

    private function validate(Cpf $rule, string $value): bool
    {
        $failed = false;
        $rule->validate('cpf', $value, function () use (&$failed) {
            $failed = true;
        });
        return ! $failed;
    }
}

