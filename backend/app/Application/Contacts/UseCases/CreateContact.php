<?php

namespace App\Application\Contacts\UseCases;

use App\Domain\Contacts\Entities\Contact as ContactEntity;
use App\Domain\Contacts\Repositories\ContactRepository;
use InvalidArgumentException;

/**
 * Caso de uso: criar contato
 */
class CreateContact
{
    public function __construct(private readonly ContactRepository $repo) {}

    public function handle(CreateContactInput $input): CreateContactOutput
    {
        $cpf = $input->cpf ? preg_replace('/\D+/', '', $input->cpf) : null;

        if ($cpf && $this->repo->existsCpfForUser($input->userId, $cpf)) {
            throw new InvalidArgumentException('CPF já cadastrado para este usuário.');
        }

        $entity = new ContactEntity(
            id: null,
            userId: $input->userId,
            name: $input->name,
            cpf: $cpf,
            email: $input->email,
            phone: $input->phone,
            address: $input->address,
        );

        $created = $this->repo->create($entity);

        return new CreateContactOutput(
            id: $created->id ?? 0,
            userId: $created->userId,
            name: $created->name,
            cpf: $created->cpf,
            email: $created->email,
            phone: $created->phone,
            address: $created->address,
        );
    }
}

class CreateContactInput
{
    public function __construct(
        public int $userId,
        public string $name,
        public ?string $cpf = null,
        public ?string $email = null,
        public ?string $phone = null,
        /** @var array<string,mixed>|null */
        public ?array $address = null,
    ) {}
}

class CreateContactOutput
{
    public function __construct(
        public int $id,
        public int $userId,
        public string $name,
        public ?string $cpf = null,
        public ?string $email = null,
        public ?string $phone = null,
        /** @var array<string,mixed>|null */
        public ?array $address = null,
    ) {}
}
