<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreCollaboratorRequest extends FormRequest
{
    public function authorize()
    {
        return true;  // Permite que qualquer usuário faça a requisição (ou adicione lógica de permissões)
    }

    public function rules()
    {
        return [
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:collaborators,email',
            'cpf'   => 'required|string|unique:collaborators,cpf|regex:/^\d{11}$/',  // CPF com 11 dígitos numéricos
            'city'  => 'required|string|max:100',
            'state' => 'required|string|max:2',
        ];
    }

    // Mensagens de erro personalizadas
    public function messages()
    {
        return [
            'cpf.regex' => 'O CPF deve ter 11 dígitos numéricos.',
            'email.unique' => 'Este email já foi cadastrado.',
            'cpf.unique' => 'Este CPF já foi cadastrado.',
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'Digite um email válido.',
            'cpf.required' => 'O CPF é obrigatório.',
            'city.required' => 'A cidade é obrigatória.',
            'state.required' => 'O estado é obrigatório.',
        ];
    }

    // Método para personalizar a resposta de erro
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();

        throw new ValidationException($validator, response()->json([
            'message' => 'Erro de validação',
            'errors' => $errors
        ], 422));
    }
}