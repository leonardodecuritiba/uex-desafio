<?php

namespace App\Application\Auth\UseCases;

use App\Domain\Auth\Repositories\UserRepository;
use Illuminate\Contracts\Hashing\Hasher;
use App\Application\Exceptions\DomainValidationException;

/**
 * Use case: Register a new user using name, email, password.
 * Responsibilities:
 * - Ensures email uniqueness via repository port
 * - Hashes password
 * - Returns Output DTO with safe fields only
 */
class RegisterUser
{
    public function __construct(private readonly UserRepository $users, private readonly Hasher $hasher) {}

    public function handle(RegisterUserInput $input): RegisterUserOutput
    {
        $existing = $this->users->findByEmail($input->email);
        if ($existing) {
            throw DomainValidationException::withMessages([
                'email' => 'E-mail já cadastrado.',
            ]);
        }

        $user = $this->users->create(
            $input->name,
            $input->email,
            $this->hasher->make($input->password)
        );

        return new RegisterUserOutput($user->id, $user->name, $user->email);
    }

    public static function input(string $name, string $email, string $password): RegisterUserInput
    {
        return new RegisterUserInput($name, $email, $password);
    }
}

class RegisterUserInput
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
    ) {}
}

class RegisterUserOutput
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
    ) {}
}
