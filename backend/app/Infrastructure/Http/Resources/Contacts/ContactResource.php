<?php

namespace App\Infrastructure\Http\Resources\Contacts;

use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'cpf' => $this->cpf,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address ?? [
                'cep' => null,
                'logradouro' => null,
                'numero' => null,
                'complemento' => null,
                'bairro' => null,
                'localidade' => null,
                'uf' => null,
                'lat' => null,
                'lng' => null,
            ],
            'created_at' => optional(data_get($this->resource, 'created_at'))?->toISOString(),
            'updated_at' => optional(data_get($this->resource, 'updated_at'))?->toISOString(),
        ];
    }
}
