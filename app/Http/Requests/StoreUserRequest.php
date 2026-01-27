<?php

namespace App\Http\Requests;

use App\Services\SectorService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
        $sectorId = $this->input('sector_id') ?: app(SectorService::class)->getDefaultSectorId();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'avatar_base64' => 'nullable|string',
            'password' => [
                'required',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',      // Pelo menos uma letra minúscula
                'regex:/[A-Z]/',      // Pelo menos uma letra maiúscula
                'regex:/[0-9]/',      // Pelo menos um número
                'regex:/[!@#$%^&*(),.?":{}|<>]/', // Pelo menos um caractere especial
            ],
            'role' => 'required|in:admin,supervisor,user',
            'sector_id' => 'nullable|uuid|exists:sectors,id',
            'teams' => 'nullable|array',
            'teams.*' => Rule::exists('teams', 'id')->where('sector_id', $sectorId),
        ];

        // Se for supervisor, validar equipes (obrigatório pelo menos uma)
        if ($this->input('role') === 'supervisor') {
            $rules['teams'] = 'required|array|min:1';
            $rules['sector_id'] = 'required|uuid|exists:sectors,id';
        }

        if ($this->input('role') === 'user') {
            $rules['sector_id'] = 'required|uuid|exists:sectors,id';
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
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
            'password.confirmed' => 'A confirmação de senha não coincide.',
            'password.regex' => 'A senha deve conter pelo menos uma letra maiúscula, uma minúscula, um número e um caractere especial.',
            'role.required' => 'O perfil é obrigatório.',
            'role.in' => 'O perfil selecionado é inválido.',
            'sector_id.required' => 'Selecione um setor.',
            'sector_id.exists' => 'O setor selecionado é inválido.',
            'teams.required' => 'Selecione pelo menos uma equipe para o supervisor.',
            'teams.array' => 'As equipes devem ser um array.',
            'teams.min' => 'Selecione pelo menos uma equipe para o supervisor.',
            'teams.*.exists' => 'Uma ou mais equipes selecionadas são inválidas.',
        ];
    }
}
