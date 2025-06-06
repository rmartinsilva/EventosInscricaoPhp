<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInscricaoRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Defina sua lógica de autorização aqui. Por padrão, permitir todas as requisições.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'evento.codigo' => 'required|integer|exists:eventos,codigo',
            'participante.codigo' => 'required|integer|exists:participantes,codigo',
            'forma_pagamento' => 'required|string|max:100',
            'cortesia' => 'nullable|boolean',
            'status' => 'required|string|size:1', // Ex: 'P' (Pendente), 'C' (Confirmada), 'X' (Cancelada)
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
            'evento.codigo.required' => 'O código do evento é obrigatório.',
            'evento.codigo.integer' => 'O código do evento deve ser um número inteiro.',
            'evento.codigo.exists' => 'O evento selecionado não existe.',
            'participante.codigo.required' => 'O código do participante é obrigatório.',
            'participante.codigo.integer' => 'O código do participante deve ser um número inteiro.',
            'participante.codigo.exists' => 'O participante selecionado não existe.',
            'forma_pagamento.required' => 'A forma de pagamento é obrigatória.',
            'forma_pagamento.string' => 'A forma de pagamento deve ser um texto.',
            'forma_pagamento.max' => 'A forma de pagamento deve ter no máximo 100 caracteres.',
            'cortesia.boolean' => 'O campo cortesia deve ser verdadeiro ou falso.',
            'status.required' => 'O status da inscrição é obrigatório.',
            'status.string' => 'O status da inscrição deve ser um texto.',
            'status.size' => 'O status da inscrição deve ter exatamente 1 caractere.',
        ];
    }
} 