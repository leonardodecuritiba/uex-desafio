<?php

namespace Tests\Unit\Application;

use App\Application\Contacts\UseCases\UpdateContact;
use App\Application\Contacts\UseCases\UpdateContactInput;
use App\Domain\Contacts\Entities\Contact as ContactEntity;
use App\Domain\Contacts\Repositories\ContactRepository;
use PHPUnit\Framework\TestCase;

class UpdateContactUseCaseTest extends TestCase
{
    public function test_merges_address_and_removes_nulls(): void
    {
        $repo = new class implements ContactRepository {
            public ContactEntity $saved;
            public function create(ContactEntity $contact): ContactEntity
            {
                return $contact;
            }
            public function existsCpfForUser(int $userId, string $cpf): bool
            {
                return false;
            }
            public function existsCpfForUserExceptId(int $userId, string $cpf, int $exceptId): bool
            {
                return false;
            }
            public function findById(int $id): ?ContactEntity
            {
                return new ContactEntity(
                    id: 1,
                    userId: 10,
                    name: 'Old',
                    cpf: null,
                    email: null,
                    phone: null,
                    address: ['cep' => '80000000', 'numero' => '123', 'complemento' => 'Ap 1', 'uf' => 'PR'],
                );
            }
            public function findByIdForUser(int $id, int $userId): ?ContactEntity
            {
                return null;
            }
            public function update(ContactEntity $contact): ContactEntity
            {
                $this->saved = $contact;
                return $contact;
            }
            public function deleteByIdForUser(int $id, int $userId): bool
            {
                return false;
            }
            public function search(int $userId, \App\Domain\Contacts\DTOs\ContactSearchFilters $filters, \App\Domain\Common\Pagination $pg, \App\Domain\Common\Sort $sort): \App\Domain\Common\PaginatedResult
            {
                return new \App\Domain\Common\PaginatedResult(items: [], total: 0, page: 1, perPage: 10, lastPage: 0, sortBy: 'created_at', order: 'desc');
            }
        };

        $uc = new UpdateContact($repo);
        $out = $uc->handle(new UpdateContactInput(
            id: 1,
            userId: 10,
            address: ['numero' => '456', 'complemento' => null],
            provided: [],
            addressProvided: true,
        ));

        $this->assertSame('456', $out->address['numero']);
        $this->assertArrayNotHasKey('complemento', $out->address);
        $this->assertSame('PR', $out->address['uf']);
    }
}
