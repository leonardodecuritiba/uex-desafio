<?php

namespace App\Application\Contacts\UseCases;

use App\Domain\Contacts\Repositories\ContactRepository;
use RuntimeException;

class DeleteContact
{
    public function __construct(private readonly ContactRepository $repo) {}

    public function handle(DeleteContactInput $input): void
    {
        $ok = $this->repo->deleteByIdForUser($input->contactId, $input->userId);
        if (! $ok) {
            throw new RuntimeException('NOT_ALLOWED_OR_NOT_FOUND');
        }
    }
}

class DeleteContactInput
{
    public function __construct(
        public int $contactId,
        public int $userId,
    ) {}
}
