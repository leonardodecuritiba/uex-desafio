<?php

namespace Tests\Unit\Application;

use App\Application\Contacts\UseCases\CreateContact;
use App\Application\Contacts\UseCases\CreateContactInput;
use App\Domain\Contacts\Entities\Contact as ContactEntity;
use App\Domain\Contacts\Repositories\ContactRepository;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CreateContactUseCaseTest extends TestCase
{
    public function test_calls_repository_and_returns_output(): void
    {
        $repo = new class implements ContactRepository {
            public function create(ContactEntity $contact): ContactEntity
            {
                return new ContactEntity(
                    id: 10,
                    userId: $contact->userId,
                    name: $contact->name,
                    cpf: $contact->cpf,
                    email: $contact->email,
                    phone: $contact->phone,
                    address: $contact->address,
                );
            }
            public function existsCpfForUser(int $userId, string $cpf): bool
            {
                return false;
            }
            public function findById(int $id): ?ContactEntity
            {
                return null;
            }
            public function findByIdForUser(int $id, int $userId): ?ContactEntity
            {
                return null;
            }
            public function update(ContactEntity $contact): ContactEntity
            {
                return $contact;
            }
            public function existsCpfForUserExceptId(int $userId, string $cpf, int $exceptId): bool
            {
                return false;
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

        $uc = new CreateContact($repo);
        $out = $uc->handle(new CreateContactInput(
            userId: 1,
            name: 'Maria',
            cpf: '529.982.247-25',
            email: 'maria@gmail.com',
            phone: '4199',
            address: ['cep' => '80000000'],
        ));

        $this->assertSame(10, $out->id);
        $this->assertSame('52998224725', $out->cpf);
    }

    public function test_duplicate_cpf_throws_exception(): void
    {
        $repo = new class implements ContactRepository {
            public function create(ContactEntity $contact): ContactEntity
            {
                return $contact;
            }
            public function existsCpfForUser(int $userId, string $cpf): bool
            {
                return true;
            }
            public function findById(int $id): ?ContactEntity
            {
                return null;
            }
            public function findByIdForUser(int $id, int $userId): ?ContactEntity
            {
                return null;
            }
            public function update(ContactEntity $contact): ContactEntity
            {
                return $contact;
            }
            public function existsCpfForUserExceptId(int $userId, string $cpf, int $exceptId): bool
            {
                return true;
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

        $uc = new CreateContact($repo);
        $this->expectException(InvalidArgumentException::class);
        $uc->handle(new CreateContactInput(userId: 1, name: 'Joao', cpf: '52998224725'));
    }
}
