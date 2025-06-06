<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateGrupoRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $grupoId = $this->route('grupo');

        return [
            'descricao' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('grupos', 'descricao')->ignore($grupoId)
            ],
        ];
    }
}
