<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Auth\Repositories\UserRepository;
use App\Models\User;

class EloquentUserRepository implements UserRepository
{
    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    public function create(string $name, string $email, string $hashedPassword): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
        ]);
    }
}

