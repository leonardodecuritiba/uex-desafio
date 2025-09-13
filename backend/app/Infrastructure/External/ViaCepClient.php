<?php

namespace App\Infrastructure\External;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ViaCepClient
{
    public function getByCep(string $cep): ?array
    {
        $base = rtrim(config('services.viacep.base_url', 'https://viacep.com.br'), '/');
        $cep = preg_replace('/\D+/', '', $cep);

        $resp = Http::timeout(3)->retry(2, 200)->get("{$base}/ws/{$cep}/json/");
        if ($resp->failed()) {
            return null;
        }
        $data = $resp->json();
        if (isset($data['erro']) && $data['erro'] == true) {
            return null;
        }
        Log::error($data);
        return [
            'cep' => $data['cep'] ?? $cep,
            'logradouro' => $data['logradouro'] ?? null,
            'bairro' => $data['bairro'] ?? null,
            'localidade' => $data['localidade'] ?? null,
            'uf' => $data['uf'] ?? null,
        ];
    }
}
