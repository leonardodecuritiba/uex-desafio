<?php

namespace App\Infrastructure\Http\Resources\Contacts;

use Illuminate\Http\Resources\Json\JsonResource;

class ContactListItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'cpf' => $this->cpf,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => [
                'localidade' => data_get($this->resource, 'address.localidade'),
                'uf' => data_get($this->resource, 'address.uf'),
                'lat' => data_get($this->resource, 'address.lat'),
                'lng' => data_get($this->resource, 'address.lng'),
            ],
        ];
    }
}

