<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInscricaoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // 'sometimes' garante que a regra só é aplicada se o campo estiver presente na request.
        return [
            'evento.codigo' => 'sometimes|required|integer|exists:eventos,codigo',
            'participante.codigo' => 'sometimes|required|integer|exists:participantes,codigo',
            'data' => 'sometimes|required|date_format:Y-m-d H:i:s',
            'forma_pagamento' => 'sometimes|required|string|max:100',
            'cortesia' => 'nullable|boolean', // Se presente, deve ser booleano. Se ausente, não é validado.
            'status' => 'sometimes|required|string|size:1',
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
            'evento_codigo.required' => 'O código do evento é obrigatório se fornecido.',
            'evento_codigo.integer' => 'O código do evento deve ser um número inteiro.',
            'evento.codigo.exists' => 'O evento selecionado não existe.',
            'participante.codigo.required' => 'O código do participante é obrigatório se fornecido.',
            'participante.codigo.integer' => 'O código do participante deve ser um número inteiro.',
            'participante.codigo.exists' => 'O participante selecionado não existe.',
            'data.required' => 'A data da inscrição é obrigatória se fornecida.',
            'data.date_format' => 'A data da inscrição deve estar no formato YYYY-MM-DD HH:MM:SS.',
            'forma_pagamento.required' => 'A forma de pagamento é obrigatória se fornecida.',
            'forma_pagamento.string' => 'A forma de pagamento deve ser um texto.',
            'forma_pagamento.max' => 'A forma de pagamento deve ter no máximo 100 caracteres.',
            'cortesia.boolean' => 'O campo cortesia deve ser verdadeiro ou falso.',
            'status.required' => 'O status da inscrição é obrigatório se fornecido.',
            'status.string' => 'O status da inscrição deve ser um texto.',
            'status.size' => 'O status da inscrição deve ter exatamente 1 caractere.',
        ];
    }
} 