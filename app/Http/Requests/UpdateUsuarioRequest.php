<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Tenta obter o ID do usuário da rota
        $userId = $this->route('usuario'); // ou $this->route('id') dependendo do nome do parâmetro na rota

        return [
            'name' => 'sometimes|required|string|max:255', // 'sometimes' para permitir atualização parcial
            'login' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('usuarios', 'login')->ignore($userId)
            ],
            'password' => 'sometimes|required|string|min:6', // Permitir atualização opcional da senha
        ];
    }
}
