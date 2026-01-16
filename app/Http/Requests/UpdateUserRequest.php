<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && $user->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');
        
        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:admin,supervisor',
            'teams' => 'nullable|array',
            'teams.*' => 'exists:teams,id',
        ];

        // Se for supervisor, validar equipes (obrigatório pelo menos uma)
        if ($this->input('role') === 'supervisor') {
            $rules['teams'] = 'required|array|min:1';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ser um endereço válido.',
            'email.unique' => 'Este email já está em uso.',
            'role.required' => 'O perfil é obrigatório.',
            'role.in' => 'O perfil selecionado é inválido.',
            'teams.required' => 'Selecione pelo menos uma equipe para o supervisor.',
            'teams.array' => 'As equipes devem ser um array.',
            'teams.min' => 'Selecione pelo menos uma equipe para o supervisor.',
            'teams.*.exists' => 'Uma ou mais equipes selecionadas são inválidas.',
        ];
    }
}
