<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateAcessoGrupoRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Assuming the route parameter is named 'acesso_grupo' for the ID of the AcessoGrupo record
        $acessoGrupoId = $this->route('acesso_grupo');

        return [
            'acesso.id' => [
                'sometimes', // Allow partial updates
                'required',
                'integer',
                'exists:acessos,id',
                Rule::unique('acesso_grupo')->where(function ($query) {
                    return $query->where('grupo_id', $this->input('grupo_id'));
                })->ignore($acessoGrupoId),
            ],
            'grupo.id' => [
                'sometimes',
                'required',
                'integer',
                'exists:grupos,id',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'acesso.id.unique' => 'Esta combinação de acesso e grupo já existe.',
        ]);
    }
} 