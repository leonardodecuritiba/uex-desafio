<?php

namespace App\Infrastructure\Http\Requests\Auth;

use App\Infrastructure\Http\Requests\BaseApiRequest;

class ResetPasswordRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required'],
            'email' => ['required', 'email:rfc,dns'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}

