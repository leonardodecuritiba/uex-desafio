<?php

namespace Tests\Unit\Infrastructure\Http\Resources;

use App\Infrastructure\Http\Resources\Contacts\ContactResource;
use PHPUnit\Framework\TestCase;

class ContactResourceTest extends TestCase
{
    public function test_maps_null_address_fields_without_errors(): void
    {
        $model = (object) [
            'id' => 10,
            'name' => 'Jose',
            'cpf' => null,
            'email' => null,
            'phone' => null,
            'address' => null,
            'created_at' => null,
            'updated_at' => null,
        ];

        $arr = (new ContactResource($model))->toArray(null);
        $this->assertArrayHasKey('address', $arr);
        $this->assertArrayHasKey('cep', $arr['address']);
        $this->assertArrayHasKey('lat', $arr['address']);
        $this->assertNull($arr['address']['cep']);
        $this->assertNull($arr['address']['lat']);
    }
}

