<?php

namespace App\Domain\Contacts\DTOs;

class ContactSearchFilters
{
    public ?string $q;
    public ?string $name;
    public ?string $cpf;
    public ?bool $hasGeo;

    public function __construct(?string $q, ?string $name, ?string $cpf, ?bool $hasGeo)
    {
        $this->q = $q !== null && $q !== '' ? $q : null;
        $this->name = $name !== null && $name !== '' ? $name : null;
        $this->cpf = $cpf !== null && $cpf !== '' ? preg_replace('/\D+/', '', $cpf) : null;
        $this->hasGeo = $hasGeo;
    }

    public function qLooksLikeCpf(): bool
    {
        return $this->q !== null && preg_match('/^\d{11}$/', preg_replace('/\D+/', '', $this->q)) === 1;
    }

    public function qDigits(): ?string
    {
        if (! $this->qLooksLikeCpf()) return null;
        return preg_replace('/\D+/', '', (string) $this->q);
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'q' => $this->q,
            'name' => $this->name,
            'cpf' => $this->cpf,
            'has_geo' => $this->hasGeo,
        ];
    }
}

