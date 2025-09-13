<?php

namespace App\Infrastructure\Geo;

use App\Domain\Geo\Geocoder;
use App\Domain\Geo\GeocodeResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleGeocoder implements Geocoder
{
    public function __construct(private readonly string $apiKey, private readonly int $timeoutMs, private readonly int $retries)
    {
    }

    public function geocode(string $normalizedAddress): ?GeocodeResult
    {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json';
        $attempts = 0;
        $backoff = 200; // ms
        do {
            $attempts++;
            $resp = Http::timeout($this->timeoutMs / 1000)
                ->acceptJson()
                ->get($url, [
                    'address' => $normalizedAddress,
                    'key' => $this->apiKey,
                ]);

            if ($resp->failed()) {
                Log::warning('geocode_http_failed', ['code' => $resp->status()]);
                $result = null;
            } else {
                $body = $resp->json();
                $status = $body['status'] ?? 'UNKNOWN_ERROR';
                if ($status === 'OK') {
                    $result = $this->pickBest($body['results'] ?? []);
                } elseif ($status === 'ZERO_RESULTS') {
                    $result = null;
                } else {
                    Log::warning('geocode_status', ['status' => $status]);
                    $result = null;
                }
            }

            if ($result !== null) {
                return $result;
            }

            if ($attempts <= $this->retries) {
                usleep($backoff * 1000);
                $backoff *= 2;
            }
        } while ($attempts <= $this->retries);

        return null;
    }

    /** @param array<int,array<string,mixed>> $results */
    private function pickBest(array $results): ?GeocodeResult
    {
        if (empty($results)) return null;
        // Prioriza location_type ROOFTOP; fallback RANGE_INTERPOLATED; caso contrário, primeiro.
        $best = null;
        foreach ($results as $r) {
            $lt = $r['geometry']['location_type'] ?? null;
            if ($lt === 'ROOFTOP') { $best = $r; break; }
            if ($lt === 'RANGE_INTERPOLATED' && $best === null) { $best = $r; }
            if ($best === null) { $best = $r; }
        }
        if (!$best) return null;
        $loc = $best['geometry']['location'] ?? null;
        if (!is_array($loc) || !isset($loc['lat'], $loc['lng'])) return null;
        return new GeocodeResult(lat: (float) $loc['lat'], lng: (float) $loc['lng'], placeId: $best['place_id'] ?? null);
    }
}

