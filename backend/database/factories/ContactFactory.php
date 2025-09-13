<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        $withGeo = true; //(bool) random_int(0, 1);
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->name(),
            'cpf' => str_pad((string) random_int(1, 99999999999), 11, '0', STR_PAD_LEFT),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->numerify('###########'),
            'address' => [
                'cep' => '80000000',
                'logradouro' => 'Rua Exemplo',
                'numero' => (string) random_int(1, 9999),
                'bairro' => 'Centro',
                'localidade' => 'Curitiba',
                'uf' => 'PR',
                'lat' => $withGeo ? -25.48631498913575 + (random_int(-50, 50) / 1000) : null,
                'lng' => $withGeo ? -49.23294363637623 + (random_int(-50, 50) / 1000) : null,
            ],
        ];
    }
}
