<?php

namespace App\Domain\Common;

/** Simple pagination value object */
class Pagination
{
    public function __construct(public int $page = 1, public int $perPage = 20) {}
}

