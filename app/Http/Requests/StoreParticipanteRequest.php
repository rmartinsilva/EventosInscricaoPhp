<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreParticipanteRequest extends BaseRequest // Usando BaseRequest como nos outros
{   
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string|size:14|unique:participantes,cpf',
            'email' => 'nullable|email|max:255',
            'data_nascimento' => 'required|date_format:Y-m-d',
            'nome_contato_emergencia' => 'required|string|max:255',
            'numero_contato_emergencia' => 'required|string|max:15',
            'telefone' => 'required|string|max:15',
            'sexo' => ['required', 'string', Rule::in(['M', 'F'])],
            'cidade' => 'required|string|max:255',
            'participante_igreja' => 'required|boolean',
            'qual_igreja' => 'nullable|string|max:255|required_if:participante_igreja,true',
            'usa_medicamento' => 'required|boolean',
            'qual_medicamento' => 'nullable|string|max:255|required_if:usa_medicamento,true',
        ];
    }
} 