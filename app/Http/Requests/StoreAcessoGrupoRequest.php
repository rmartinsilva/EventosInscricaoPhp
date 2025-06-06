<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreAcessoGrupoRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'acesso.id' => [
                'required',
                'integer',
                'exists:acessos,id',
                Rule::unique('acesso_grupo', 'acesso_id')->where(function ($query) {
                    return $query->where('grupo_id', $this->input('grupo.id'));
                }),
            ],
            'grupo.id' => [
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