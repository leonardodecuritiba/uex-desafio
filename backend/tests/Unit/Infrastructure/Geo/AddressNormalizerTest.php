<?php

namespace Tests\Unit\Infrastructure\Geo;

use App\Infrastructure\Geo\AddressNormalizer;
use PHPUnit\Framework\TestCase;

class AddressNormalizerTest extends TestCase
{
    public function test_normalize_builds_string_with_optional_parts(): void
    {
        $addr = [
            'logradouro' => 'Rua Exemplo',
            'numero' => '123',
            'bairro' => 'Centro',
            'localidade' => 'Curitiba',
            'uf' => 'PR',
            'cep' => '80000-000',
        ];
        $n = AddressNormalizer::normalize($addr);
        $this->assertSame('Rua Exemplo 123, Centro, Curitiba, PR, CEP 80000000, Brasil', $n);
    }

    public function test_isEligible_rules(): void
    {
        $this->assertTrue(AddressNormalizer::isEligible(['cep' => '80000-000', 'numero' => '100']));
        $this->assertTrue(AddressNormalizer::isEligible(['logradouro' => 'Rua', 'numero' => '1', 'localidade' => 'Curitiba', 'uf' => 'PR']));
        $this->assertFalse(AddressNormalizer::isEligible(['cep' => '80000-000']));
        $this->assertFalse(AddressNormalizer::isEligible(null));
    }
}

