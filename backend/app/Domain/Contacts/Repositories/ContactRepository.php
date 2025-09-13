<?php

namespace App\Domain\Contacts\Repositories;

use App\Domain\Contacts\Entities\Contact;
use App\Domain\Contacts\DTOs\ContactSearchFilters;
use App\Domain\Common\Pagination;
use App\Domain\Common\Sort;
use App\Domain\Common\PaginatedResult;

interface ContactRepository
{
    public function create(Contact $contact): Contact;
    public function existsCpfForUser(int $userId, string $cpf): bool;
    public function findById(int $id): ?Contact;
    public function findByIdForUser(int $id, int $userId): ?Contact;
    public function update(Contact $contact): Contact;
    public function existsCpfForUserExceptId(int $userId, string $cpf, int $exceptId): bool;
    public function deleteByIdForUser(int $id, int $userId): bool;
    public function search(int $userId, ContactSearchFilters $filters, Pagination $pg, Sort $sort): PaginatedResult;
}
