<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateEventoRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $codigo = $this->route('evento');
        
        return [
            'descricao' => 'required|string|max:255',
            'data' => 'required|date_format:Y-m-d',
            'data_inicio_inscricoes' => 'required|date_format:Y-m-d H:i:s',
            'data_final_inscricoes' => 'required|date_format:Y-m-d H:i:s|after_or_equal:data_inicio_inscricoes',
            'numero_inscricoes' => 'required|integer|min:0',
            'cortesias' => 'required|boolean',
            'numero_cortesia' => 'nullable|integer|min:0|required_if:cortesias,true',
            'link_obrigado' => 'nullable|string|url',
            'url' => [
                'required',
                'string',
                'max:255',
                Rule::unique('eventos', 'url')->ignore($codigo, 'codigo')
            ],
            'valor' => 'sometimes|required|decimal:2',
        ];
    }
}
