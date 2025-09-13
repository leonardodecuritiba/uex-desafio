<?php

namespace App\Domain\Common;

/**
 * @template T
 */
class PaginatedResult
{
    /**
     * @param array<int,mixed> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $perPage,
        public int $lastPage,
        public string $sortBy,
        public string $order,
    ) {}
}

