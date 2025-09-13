<?php

namespace App\Infrastructure\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;

class Cpf implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cpf = preg_replace('/\D+/', '', (string) $value);
        if (! $this->isValidCpf($cpf)) {
            $fail('CPF inválido.');
        }
    }

    private function isValidCpf(?string $cpf): bool
    {
        if (! $cpf || strlen($cpf) !== 11) {
            return false;
        }
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $soma = 0;
            for ($i = 0, $peso = $t + 1; $i < $t; $i++, $peso--) {
                $soma += ((int) $cpf[$i]) * $peso;
            }

            $dig = ($soma * 10) % 11;
            if ($dig === 10) {
                $dig = 0;
            }

            if ((int) $cpf[$t] !== $dig) {
                return false;
            }
        }


        return true;
    }
}
