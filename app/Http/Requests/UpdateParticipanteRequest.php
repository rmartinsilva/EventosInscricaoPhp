<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateParticipanteRequest extends BaseRequest // Usando BaseRequest como nos outros
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Aberto, sem autenticação conforme solicitado
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $participanteCodigo = $this->route('participante'); // 'participante' é o nome do parâmetro na rota

        return [
            'nome' => 'required|string|max:255',
            'cpf' => ['required', 'string', 'size:14', Rule::unique('participantes', 'cpf')->ignore($participanteCodigo, 'codigo')],
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