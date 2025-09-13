<?php

namespace App\Infrastructure\Http\Requests\Account;

use App\Infrastructure\Http\Requests\BaseApiRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DeleteAccountRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Validação da senha atual usando o guard padrão (web)
            'password' => ['required', 'string', 'current_password:web'],
        ];
    }

    /**
     * Para este endpoint, senha incorreta deve retornar 403 (não 422).
     */
    protected function failedValidation(Validator $validator)
    {
        // Verifica se a falha foi especificamente da regra current_password
        $failed = $validator->failed();
        if (isset($failed['password']) && array_key_exists('CurrentPassword', $failed['password'])) {
            throw new HttpResponseException(response()->json([
                'message' => 'Senha atual incorreta.',
            ], 403));
        }

        parent::failedValidation($validator);
    }
}
