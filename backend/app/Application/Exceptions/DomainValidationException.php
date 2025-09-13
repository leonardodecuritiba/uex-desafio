<?php

namespace App\Application\Exceptions;

use RuntimeException;
use Illuminate\Support\MessageBag;

class DomainValidationException extends RuntimeException
{
    /** @var array<string, array<int, string>> */
    public array $errors;

    /**
     * @param array<string, array<int, string>> $errors
     */
    public function __construct(array $errors, string $message = 'Validation failed')
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    public static function withMessages(array $messages): self
    {
        // Normalize values to arrays
        $normalized = [];
        foreach ($messages as $field => $msg) {
            $normalized[$field] = is_array($msg) ? array_values($msg) : [(string) $msg];
        }
        return new self($normalized);
    }
}

