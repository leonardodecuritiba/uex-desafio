<?php

namespace App\Domain\Auth\Repositories;

use App\Models\User;

interface UserRepository
{
    public function findByEmail(string $email): ?User;

    public function create(string $name, string $email, string $hashedPassword): User;
}

