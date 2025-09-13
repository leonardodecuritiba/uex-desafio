<?php

namespace App\Infrastructure\Http\Requests\Auth;

use App\Infrastructure\Http\Requests\BaseApiRequest;

class LoginRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc,dns'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}

