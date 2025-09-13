<?php

namespace App\Application\Contacts\UseCases;

use App\Domain\Contacts\Entities\Contact as ContactEntity;
use App\Domain\Contacts\Repositories\ContactRepository;

/**
 * Caso de uso: visualizar um contato do usuário autenticado.
 * - Filtra por id e user_id via repositório.
 * - Retorna null quando não encontrado ou não pertence ao usuário (anti-enumeração).
 */
class ShowContact
{
    public function __construct(private readonly ContactRepository $repo) {}

    public function handle(int $id, int $userId): ?ContactEntity
    {
        return $this->repo->findByIdForUser($id, $userId);
    }
}

