<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreUsuarioRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'login' => [
                'required',
                'string',
                'max:255',
                Rule::unique('usuarios', 'login')
            ],
            'password' => 'required|string|min:6',
        ];
    }
}

