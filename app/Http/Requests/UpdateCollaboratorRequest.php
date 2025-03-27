<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class UpdateCollaboratorRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $collaboratorId = $this->route('id');

        return [
            'name'  => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:collaborators,email,' . $collaboratorId,
            'cpf'   => 'sometimes|required|string|unique:collaborators,cpf,' . $collaboratorId . '|regex:/^\d{11}$/',
            'city'  => 'sometimes|required|string|max:100',
            'state' => 'sometimes|required|string|max:2',
        ];
    }

    // Mensagens de erro personalizadas
    public function messages()
    {
        return [
            'cpf.regex' => 'O CPF deve ter 11 dígitos numéricos.',
            'email.unique' => 'Este email já está em uso.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
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