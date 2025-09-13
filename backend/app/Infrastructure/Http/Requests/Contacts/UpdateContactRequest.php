<?php

namespace App\Infrastructure\Http\Requests\Contacts;

use App\Infrastructure\Http\Requests\BaseApiRequest;
use App\Infrastructure\Validation\Rules\Cpf as CpfRule;

class UpdateContactRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id ?? 0;
        $id = (int) $this->route('id');
        return [
            'name' => ['sometimes', 'string', 'min:2', 'max:120'],
            'cpf' => ['sometimes', 'string', 'size:11', new CpfRule(), 'unique:contacts,cpf,' . $id . ',id,user_id,' . $userId],
            'email' => ['sometimes', 'nullable', 'email:rfc,dns', 'max:150'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'address' => ['sometimes', 'array'],
            'address.cep' => ['sometimes', 'string', 'regex:/^\d{8}$/'],
            'address.logradouro' => ['sometimes', 'string', 'min:2', 'max:150'],
            'address.numero' => ['sometimes', 'string', 'min:1', 'max:20'],
            'address.complemento' => ['sometimes', 'nullable', 'string', 'max:150'],
            'address.bairro' => ['sometimes', 'string', 'min:2', 'max:100'],
            'address.localidade' => ['sometimes', 'string', 'min:2', 'max:120'],
            'address.uf' => ['sometimes', 'string', 'size:2', 'uppercase'],
            'version' => ['sometimes', 'nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'cpf.size' => 'CPF deve conter 11 dígitos.',
            'cpf.unique' => 'CPF já cadastrado para este usuário.',
            'address.cep.regex' => 'CEP deve conter 8 dígitos.',
            'address.uf.size' => 'UF deve conter 2 caracteres.',
        ];
    }
}
