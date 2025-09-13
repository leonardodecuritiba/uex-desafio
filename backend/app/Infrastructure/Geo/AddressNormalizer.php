<?php

namespace App\Infrastructure\Geo;

class AddressNormalizer
{
    /** @param array<string,mixed> $addr */
    public static function normalize(array $addr): string
    {
        $cep = isset($addr['cep']) ? preg_replace('/\D+/', '', (string) $addr['cep']) : '';
        $logradouro = trim(((string)($addr['logradouro'] ?? '')) . ' ' . ((string)($addr['numero'] ?? '')));
        $parts = array_filter([
            $logradouro ?: null,
            $addr['bairro'] ?? null,
            $addr['localidade'] ?? null,
            $addr['uf'] ?? null,
            $cep ? ('CEP ' . $cep) : null,
            'Brasil',
        ]);
        return implode(', ', $parts);
    }

    /** @param array<string,mixed>|null $addr */
    public static function isEligible(?array $addr): bool
    {
        if (!is_array($addr)) return false;
        $cep = isset($addr['cep']) ? preg_replace('/\D+/', '', (string) $addr['cep']) : '';
        $numero = trim((string)($addr['numero'] ?? ''));
        $logradouro = trim((string)($addr['logradouro'] ?? ''));
        $localidade = trim((string)($addr['localidade'] ?? ''));
        $uf = trim((string)($addr['uf'] ?? ''));

        if ($cep && $numero) return true;
        if ($logradouro && $numero && $localidade && $uf) return true;
        return false;
    }
}

