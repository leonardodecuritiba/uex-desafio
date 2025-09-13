<?php

namespace App\Infrastructure\Http\Controllers\Address;

use App\Http\Controllers\Controller;
use App\Infrastructure\External\ViaCepClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CepLookupController extends Controller
{
    public function __construct(private readonly ViaCepClient $client) {}

    public function __invoke(string $cep): JsonResponse
    {
        $cep = preg_replace('/\D+/', '', $cep);
        $key = 'cache:ceps:' . $cep;

        $data = Cache::get($key);
        if (!$data) {
            $data = $this->client->getByCep($cep);
            if (! $data) {
                return response()->json(['message' => 'CEP não encontrado.'], 404);
            }
            Cache::put($key, $data, now()->addDay());
        }

        return response()->json(['data' => $data]);
    }
}
