<?php

namespace App\Domain\Common;

/** Simple sort value object */
class Sort
{
    public function __construct(public string $by = 'created_at', public string $order = 'desc') {}
}

