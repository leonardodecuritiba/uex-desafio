<?php

namespace App\Domain\Contacts\Entities;

class Contact
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $name,
        public ?string $cpf = null,
        public ?string $email = null,
        public ?string $phone = null,
        /** @var array<string,mixed>|null */
        public ?array $address = null,
    ) {
    }
}

