<?php

namespace App\Application\Contacts\UseCases;

use App\Domain\Contacts\Entities\Contact as ContactEntity;
use App\Domain\Contacts\Repositories\ContactRepository;
use InvalidArgumentException;
use RuntimeException;

/**
 * Caso de uso: atualizar contato (parcial)
 * - Faz merge profundo do address; remove chaves com null.
 * - Normaliza CPF (somente dígitos) e valida unicidade por usuário ignorando o próprio id.
 */
class UpdateContact
{
    public function __construct(private readonly ContactRepository $repo) {}

    public function handle(UpdateContactInput $input): UpdateContactOutput
    {
        $current = $this->repo->findById($input->id);
        if (! $current) {
            throw new RuntimeException('NOT_FOUND');
        }

        // Monta entidade com campos existentes e aplica alterações parciais
        $name = $input->name ?? $current->name;
        $cpf = array_key_exists('cpf', $input->provided)
            ? ($input->cpf ? preg_replace('/\D+/', '', $input->cpf) : null)
            : $current->cpf;
        $email = $input->email ?? $current->email;
        $phone = $input->phone ?? $current->phone;
        $address = $this->mergeAddress($current->address ?? [], $input->addressProvided ? ($input->address ?? []) : null);

        if ($cpf && $this->repo->existsCpfForUserExceptId($current->userId, $cpf, $current->id ?? 0)) {
            throw new InvalidArgumentException('CPF já cadastrado para este usuário.');
        }

        $entity = new ContactEntity(
            id: $current->id,
            userId: $current->userId,
            name: $name,
            cpf: $cpf,
            email: $email,
            phone: $phone,
            address: $address,
        );

        $saved = $this->repo->update($entity);

        return new UpdateContactOutput(
            id: $saved->id ?? 0,
            userId: $saved->userId,
            name: $saved->name,
            cpf: $saved->cpf,
            email: $saved->email,
            phone: $saved->phone,
            address: $saved->address,
        );
    }

    /** @param array<string,mixed> $current */
    private function mergeAddress(array $current, ?array $incoming): ?array
    {
        if ($incoming === null) {
            return $current ?: null;
        }

        $merged = $current;
        foreach ($incoming as $key => $value) {
            if ($value === null) {
                unset($merged[$key]);
            } else {
                $merged[$key] = $value;
            }
        }
        return $merged ?: null;
    }
}

class UpdateContactInput
{
    /** @param array<string,bool> $provided */
    public function __construct(
        public int $id,
        public int $userId,
        public ?string $name = null,
        public ?string $cpf = null,
        public ?string $email = null,
        public ?string $phone = null,
        /** @var array<string,mixed>|null */
        public ?array $address = null,
        /** flags para saber o que veio no payload */
        public array $provided = [],
        public bool $addressProvided = false,
    ) {}
}

class UpdateContactOutput
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
