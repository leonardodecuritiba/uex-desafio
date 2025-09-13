<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $count = 50;

        // Garante pelo menos um usuário para associar contatos
        $users = User::query()->take(3)->get();
        if ($users->isEmpty()) {
            $users = collect([
                User::factory()->create(['email' => 'silva.zanin@gmail.com', 'password' => bcrypt('secret123')]),
            ]);
        }

        foreach ($users as $user) {
            Contact::factory()
                ->count($count)
                ->state(function () use ($user) {
                    return [
                        'user_id' => $user->id
                    ];
                })
                ->create();
        }
    }
}
