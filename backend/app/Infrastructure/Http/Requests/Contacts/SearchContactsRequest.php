<?php

namespace App\Infrastructure\Http\Requests\Contacts;

use App\Infrastructure\Http\Requests\BaseApiRequest;

class SearchContactsRequest extends BaseApiRequest
{
    protected function prepareForValidation(): void
    {
        $normalized = [];
        if ($this->has('has_geo')) {
            $raw = strtolower((string) $this->query('has_geo'));
            if ($raw === 'true') {
                $normalized['has_geo'] = 1;
            } elseif ($raw === 'false') {
                $normalized['has_geo'] = 0;
            }
        }
        if ($this->has('cpf')) {
            $normalized['cpf'] = preg_replace('/\D+/', '', (string) $this->query('cpf'));
        }
        if (!empty($normalized)) {
            $this->merge($normalized);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'min:1', 'max:100'],
            'name' => ['nullable', 'string', 'min:2', 'max:120'],
            'cpf' => ['nullable', 'string', 'size:11'],
            'has_geo' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort' => ['nullable', 'in:created_at,name'],
            'order' => ['nullable', 'in:asc,desc'],
        ];
    }
}
