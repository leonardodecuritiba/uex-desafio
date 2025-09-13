<?php

namespace App\Infrastructure\Http\Requests\Contacts;

use App\Infrastructure\Http\Requests\BaseApiRequest;
use App\Infrastructure\Validation\Rules\Cpf as CpfRule;

class CreateContactRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id ?? 0;

        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'cpf' => ['required', 'string', 'size:11', new CpfRule(), 'unique:contacts,cpf,NULL,id,user_id,' . $userId],
            'email' => ['nullable', 'email:rfc,dns', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'array'],
            'address.cep' => ['required', 'string', 'regex:/^\d{8}$/'],
            'address.logradouro' => ['required', 'string', 'min:2', 'max:150'],
            'address.numero' => ['required', 'string', 'min:1', 'max:20'],
            'address.complemento' => ['nullable', 'string', 'max:150'],
            'address.bairro' => ['required', 'string', 'min:2', 'max:100'],
            'address.localidade' => ['required', 'string', 'min:2', 'max:120'],
            'address.uf' => ['required', 'string', 'size:2', 'uppercase']
        ];
    }

    public function messages(): array
    {
        return [
            'cpf.required' => 'CPF é obrigatório.',
            'cpf.size' => 'CPF deve conter 11 dígitos.',
            'cpf.unique' => 'CPF já cadastrado para este usuário.',
            'address.required' => 'Endereço é obrigatório.',
            'address.cep.required' => 'CEP é obrigatório.',
            'address.cep.regex' => 'CEP deve conter 8 dígitos.',
            'address.logradouro.required' => 'Logradouro é obrigatório.',
            'address.logradouro.min' => 'Logradouro deve conter ao menos 2 caracteres.',
            'address.numero.required' => 'Número é obrigatório.',
            'address.bairro.required' => 'Bairro é obrigatório.',
            'address.localidade.required' => 'Cidade é obrigatória.',
            'address.uf.required' => 'UF é obrigatória.',
            'address.uf.size' => 'UF deve conter 2 caracteres.'
        ];
    }
}
