<?php

namespace Tests\Unit\Infrastructure\Http\Resources;

use App\Infrastructure\Http\Resources\Contacts\ContactListItemResource;
use PHPUnit\Framework\TestCase;

class ContactListItemResourceTest extends TestCase
{
    public function test_formats_address_and_keeps_lat_lng(): void
    {
        $model = (object) [
            'id' => 1,
            'name' => 'Maria',
            'cpf' => '52998224725',
            'email' => 'maria@example.com',
            'phone' => '4199',
            'address' => [
                'localidade' => 'Curitiba',
                'uf' => 'PR',
                'lat' => -25.42,
                'lng' => -49.27,
            ],
        ];

        $res = (new ContactListItemResource($model))->toArray(null);

        $this->assertSame('Curitiba', $res['address']['localidade']);
        $this->assertSame('PR', $res['address']['uf']);
        $this->assertSame(-25.42, $res['address']['lat']);
        $this->assertSame(-49.27, $res['address']['lng']);
    }
}
