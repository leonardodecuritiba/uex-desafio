<?php

namespace App\Application\Contacts\Services;

use App\Domain\Geo\Geocoder;
use App\Infrastructure\Geo\AddressNormalizer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ContactGeocodeOrchestrator
{
    public function __construct(private readonly Geocoder $geocoder) {}

    /** @param array<string,mixed>|null $address */
    public function onCreate(?array $address): ?array
    {
        if (!config('geo.on_create')) return $address;
        if (!AddressNormalizer::isEligible($address)) return $address;
        return $this->geocodeAndMerge($address ?? []);
    }

    /**
     * @param array<string,mixed>|null $old
     * @param array<string,mixed>|null $next
     * @return array<string,mixed>|null
     */
    public function onUpdate(?array $old, ?array $next): ?array
    {
        if (!config('geo.on_update')) return $next;
        if (!is_array($next)) return $next;
        if (!$this->hasRelevantChange($old ?? [], $next)) return $next;
        if (!AddressNormalizer::isEligible($next)) return $next;
        return $this->geocodeAndMerge($next);
    }

    /** @param array<string,mixed> $addr */
    private function geocodeAndMerge(array $addr): array
    {
        $norm = AddressNormalizer::normalize($addr);
        $cacheKey = 'geo:' . sha1($norm);
        $ttl = (int) config('geo.cache_ttl');
        $cached = Cache::get($cacheKey);
        if (is_array($cached) && isset($cached['lat'], $cached['lng'])) {
            Log::info('geocode_cache_hit');
            return $this->mergeMeta($addr, (float) $cached['lat'], (float) $cached['lng'], $cached['place_id'] ?? null);
        }

        $res = $this->geocoder->geocode($norm);
        if ($res) {
            Cache::put($cacheKey, ['lat' => $res->lat, 'lng' => $res->lng, 'place_id' => $res->placeId], $ttl);
            return $this->mergeMeta($addr, $res->lat, $res->lng, $res->placeId);
        }
        return $addr; // degrade gracioso
    }

    /** @param array<string,mixed> $addr */
    private function mergeMeta(array $addr, float $lat, float $lng, ?string $placeId): array
    {
        $addr['lat'] = $lat;
        $addr['lng'] = $lng;
        if ($placeId) $addr['place_id'] = $placeId;
        $addr['source'] = 'google';
        $addr['geo_ts'] = now()->toISOString();
        return $addr;
    }

    /** @param array<string,mixed> $old @param array<string,mixed> $next */
    private function hasRelevantChange(array $old, array $next): bool
    {
        $keys = ['cep','logradouro','numero','bairro','localidade','uf'];
        foreach ($keys as $k) {
            $ov = $old[$k] ?? null;
            $nv = $next[$k] ?? null;
            if ($ov !== $nv) return true;
        }
        return false;
    }
}

