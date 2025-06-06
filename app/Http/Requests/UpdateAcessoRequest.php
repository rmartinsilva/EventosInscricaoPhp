<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateAcessoRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $acessoId = $this->route('acesso');

        return [
            'descricao' => 'sometimes|required|string|max:255',
            'menu' => 'nullable|string|max:255',
            'key' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('acessos', 'key')->ignore($acessoId)
            ],
        ];
    }
}
