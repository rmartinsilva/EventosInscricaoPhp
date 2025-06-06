<?php

namespace App\Http\Requests;


class StorePagamentoPixRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Ajustar conforme a lógica de autorização
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
            'payer' => 'required|array',
            'payer.email' => 'required|email',
            'payer.first_name' => 'required|string|max:100',
            'payer.last_name' => 'required|string|max:100',
            'payer.identification' => 'required|array',
            'payer.identification.type' => 'required|string', // Ex: CPF, CNPJ
            'payer.identification.number' => 'required|string',
            'date_of_expiration' => 'nullable|date_format:Y-m-d\TH:i:s.vP|after:now', // Formato ISO8601, opcional
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    /*
     public function messages(): array
    {
        return [
            'evento_codigo.required' => 'O código do evento é obrigatório.',
            'participante_codigo.required' => 'O código do participante é obrigatório.',
            'valor.required' => 'O valor do pagamento é obrigatório.',
            'payer.email.required' => 'O email do pagador é obrigatório.',
            'payer.first_name.required' => 'O nome do pagador é obrigatório.',
            'payer.last_name.required' => 'O sobrenome do pagador é obrigatório.',
            'payer.identification.type.required' => 'O tipo de documento do pagador é obrigatório.',
            'payer.identification.number.required' => 'O número do documento do pagador é obrigatório.',
            'date_of_expiration.date_format' => 'A data de expiração do PIX deve estar no formato ISO8601 (ex: YYYY-MM-DDTHH:MM:SS.mmmZ).',
            'date_of_expiration.after' => 'A data de expiração do PIX deve ser uma data futura.',
        ];
    }

    */
}
