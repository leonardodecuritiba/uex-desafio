<?php

namespace Tests\Unit\Domain\Contacts;

use App\Domain\Contacts\DTOs\ContactSearchFilters;
use PHPUnit\Framework\TestCase;

class ContactSearchFiltersTest extends TestCase
{
    public function test_normalizes_cpf_and_detects_q_as_cpf(): void
    {
        $filters = new ContactSearchFilters(q: '529.982.247-25', name: null, cpf: '529.982.247-25', hasGeo: null);
        $this->assertTrue($filters->qLooksLikeCpf());
        $this->assertSame('52998224725', $filters->qDigits());
        $this->assertSame('52998224725', $filters->cpf); // normalized on construct
    }

    public function test_q_non_numeric_is_not_cpf(): void
    {
        $filters = new ContactSearchFilters(q: 'maria', name: null, cpf: null, hasGeo: null);
        $this->assertFalse($filters->qLooksLikeCpf());
        $this->assertNull($filters->qDigits());
    }
}

