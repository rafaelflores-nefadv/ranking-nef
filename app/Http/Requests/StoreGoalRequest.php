<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGoalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Goal::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'scope' => ['required', Rule::in(['global', 'team', 'seller'])],
            'season_id' => 'required|uuid|exists:seasons,id',
            'team_id' => [
                'nullable',
                'uuid',
                'exists:teams,id',
                'required_if:scope,team',
                'prohibited_if:scope,global',
                'prohibited_if:scope,seller',
            ],
            'seller_id' => [
                'nullable',
                'uuid',
                'exists:sellers,id',
                'required_if:scope,seller',
                'prohibited_if:scope,global',
                'prohibited_if:scope,team',
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_value' => 'required|numeric|min:0',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after_or_equal:starts_at',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'scope.required' => 'O escopo da meta é obrigatório.',
            'scope.in' => 'O escopo deve ser global, team ou seller.',
            'season_id.required' => 'A temporada é obrigatória.',
            'season_id.exists' => 'A temporada selecionada não existe.',
            'team_id.required_if' => 'A equipe é obrigatória quando o escopo é "team".',
            'team_id.prohibited_if' => 'A equipe não deve ser informada quando o escopo é "global" ou "seller".',
            'seller_id.required_if' => 'O vendedor é obrigatório quando o escopo é "seller".',
            'seller_id.prohibited_if' => 'O vendedor não deve ser informado quando o escopo é "global" ou "team".',
            'name.required' => 'O nome da meta é obrigatório.',
            'target_value.required' => 'O valor alvo é obrigatório.',
            'target_value.min' => 'O valor alvo deve ser maior ou igual a zero.',
            'starts_at.required' => 'A data de início é obrigatória.',
            'ends_at.required' => 'A data de término é obrigatória.',
            'ends_at.after_or_equal' => 'A data de término deve ser igual ou posterior à data de início.',
        ];
    }
}
