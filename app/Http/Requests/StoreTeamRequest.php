<?php

namespace App\Http\Requests;

use App\Services\SectorService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Team::class);
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('teams', 'name')->where('sector_id', $sectorId),
            ],
            'display_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'sector_id' => $user && $user->role === 'admin'
                ? 'required|uuid|exists:sectors,id'
                : 'prohibited',
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
            'name.required' => 'O nome da equipe é obrigatório.',
            'name.max' => 'O nome da equipe não pode ter mais de 255 caracteres.',
            'name.unique' => 'Já existe uma equipe com este nome.',
            'display_name.max' => 'O nome de exibição não pode ter mais de 255 caracteres.',
            'sector_id.required' => 'Selecione um setor.',
            'sector_id.exists' => 'O setor selecionado é inválido.',
        ];
    }
}
