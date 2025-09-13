<?php

return [
    'on_create' => (bool) env('GEOCODE_ON_CREATE', true),
    'on_update' => (bool) env('GEOCODE_ON_UPDATE', true),
    'timeout_ms' => (int) env('GEOCODE_TIMEOUT_MS', 2500),
    'retries' => (int) env('GEOCODE_RETRIES', 1),
    'cache_ttl' => (int) env('GEOCODE_CACHE_TTL', 7 * 24 * 60 * 60),
];

