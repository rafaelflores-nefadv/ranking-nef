<?php

namespace App\Http\Requests;

use App\Services\SectorService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSellerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Seller::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        $sectorId = $user && $user->role === 'admin'
            ? $this->input('sector_id')
            : ($user?->sector_id ?? app(SectorService::class)->getDefaultSectorId());

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('sellers')->where('sector_id', $sectorId),
            ],
            'sector_id' => $user && $user->role === 'admin'
                ? 'required|uuid|exists:sectors,id'
                : 'prohibited',
            'external_code' => [
                'nullable',
                'string',
                Rule::unique('sellers')->where('sector_id', $sectorId),
            ],
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'avatar_base64' => 'nullable|string',
            'teams' => 'required|array|min:1',
            'teams.*' => Rule::exists('teams', 'id')->where('sector_id', $sectorId),
            'season_id' => 'nullable|exists:seasons,id',
            'status' => 'required|in:active,inactive',
        ];
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
            'sector_id.required' => 'O setor é obrigatório.',
            'sector_id.exists' => 'O setor selecionado é inválido.',
            'external_code.unique' => 'Este código externo já está em uso.',
            'teams.required' => 'Selecione ao menos uma equipe.',
            'teams.min' => 'Selecione ao menos uma equipe.',
            'teams.array' => 'As equipes devem ser um array.',
            'teams.*.exists' => 'Uma das equipes selecionadas não existe.',
            'season_id.exists' => 'A temporada selecionada não existe.',
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status selecionado é inválido.',
        ];
    }
}
