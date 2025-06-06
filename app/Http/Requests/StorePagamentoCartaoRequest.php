<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePagamentoCartaoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Ajustar conforme a lógica de autorização do seu sistema
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'evento_codigo' => 'required|integer|exists:eventos,codigo',
            'participante_codigo' => 'required|integer|exists:participantes,codigo',
            'valor' => 'required|numeric|min:0.01',
            'descricao_pagamento' => 'nullable|string|max:255',
            'card_token' => 'required|string',
            'installments' => 'required|integer|min:1',
            'payment_method_id' => 'required|string',
            'payer' => 'required|array',
            'payer.email' => 'required|email',
            'payer.identification' => 'nullable|array',
            'payer.identification.type' => 'required_with:payer.identification.number|string',
            'payer.identification.number' => 'required_with:payer.identification.type|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'evento_codigo.required' => 'O código do evento é obrigatório.',
            'evento_codigo.exists' => 'O evento selecionado não existe.',
            'participante_codigo.required' => 'O código do participante é obrigatório.',
            'participante_codigo.exists' => 'O participante selecionado não existe.',
            'valor.required' => 'O valor do pagamento é obrigatório.',
            'valor.numeric' => 'O valor do pagamento deve ser um número.',
            'valor.min' => 'O valor do pagamento deve ser no mínimo 0.01.',
            'card_token.required' => 'O token do cartão é obrigatório.',
            'installments.required' => 'O número de parcelas é obrigatório.',
            'installments.integer' => 'O número de parcelas deve ser um inteiro.',
            'installments.min' => 'O número de parcelas deve ser no mínimo 1.',
            'payment_method_id.required' => 'O ID do método de pagamento (bandeira) é obrigatório.',
            'payer.email.required' => 'O email do pagador é obrigatório.',
            'payer.email.email' => 'O email do pagador deve ser um endereço de email válido.',
            'payer.identification.type.required_with' => 'O tipo de documento é obrigatório quando o número do documento é fornecido.',
            'payer.identification.number.required_with' => 'O número do documento é obrigatório quando o tipo do documento é fornecido.',
        ];
    }
}
