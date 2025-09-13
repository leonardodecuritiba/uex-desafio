<?php

namespace App\Domain\Geo;

class GeocodeRequest
{
    /** @param array<string,mixed> $address */
    public function __construct(
        public array $address,
    ) {}
}

class GeocodeResult
{
    public function __construct(
        public float $lat,
        public float $lng,
        public ?string $placeId = null,
        public string $source = 'google',
    ) {}
}

interface Geocoder
{
    /**
     * Geocodifica um endereço normalizado (string) e retorna coordenadas ou null quando não encontrado.
     */
    public function geocode(string $normalizedAddress): ?GeocodeResult;
}

