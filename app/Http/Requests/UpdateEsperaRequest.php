<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEsperaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Normalmente, você colocaria lógica de autorização aqui
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'evento_codigo' => 'sometimes|integer|exists:eventos,codigo',
            // 'participante_codigo' => 'sometimes|integer|exists:participantes,codigo',
            'evento.codigo' => 'sometimes|integer|exists:eventos,codigo',
            'participante.codigo' => 'sometimes|integer|exists:participantes,codigo',
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
            'evento.codigo.integer' => 'O código do evento deve ser um número inteiro.',
            'evento.codigo.exists' => 'O evento selecionado não existe.',
            'participante.codigo.integer' => 'O código do participante deve ser um número inteiro.',
            'participante.codigo.exists' => 'O participante selecionado não existe.',
        ];
    }
} 