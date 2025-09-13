<?php

namespace App\Application\Contacts\UseCases;

use App\Domain\Common\Pagination;
use App\Domain\Common\PaginatedResult;
use App\Domain\Common\Sort;
use App\Domain\Contacts\DTOs\ContactSearchFilters;
use App\Domain\Contacts\Repositories\ContactRepository;

/**
 * Caso de uso: buscar contatos por filtros com paginação e ordenação.
 */
class SearchContacts
{
    public function __construct(private readonly ContactRepository $repo) {}

    public function handle(int $userId, ContactSearchFilters $filters, Pagination $pg, Sort $sort): PaginatedResult
    {
        return $this->repo->search($userId, $filters, $pg, $sort);
    }
}

