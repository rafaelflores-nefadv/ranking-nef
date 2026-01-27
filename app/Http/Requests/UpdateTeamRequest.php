<?php

namespace App\Http\Requests;

use App\Services\SectorService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $team = $this->route('team');
        return $this->user()->can('update', $team);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $team = $this->route('team');
        $user = $this->user();
        $sectorId = $user && $user->role === 'admin'
            ? ($this->input('sector_id') ?: $team->sector_id)
            : ($user?->sector_id ?? app(SectorService::class)->getDefaultSectorId());
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('teams', 'name')->where('sector_id', $sectorId)->ignore($team->id),
            ],
            'sector_id' => $user && $user->role === 'admin'
                ? ['required', 'uuid', Rule::in([$team->sector_id])]
                : 'prohibited',
            'sellers' => ['nullable', 'array'],
            'sellers.*' => [Rule::exists('sellers', 'id')->where('sector_id', $sectorId)],
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
            'sector_id.required' => 'Selecione um setor.',
            'sector_id.in' => 'A equipe não pertence ao setor selecionado.',
            'sellers.*.exists' => 'Um dos vendedores selecionados não pertence ao setor selecionado.',
        ];
    }
}
