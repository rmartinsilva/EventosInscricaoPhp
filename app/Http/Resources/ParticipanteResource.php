<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParticipanteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'codigo' => $this->codigo,
            'nome' => $this->nome,
            'cpf' => $this->cpf,
            'email' => $this->email,
            'data_nascimento' => $this->data_nascimento->format('Y-m-d'), // Model cast já formata
            'nome_contato_emergencia' => $this->nome_contato_emergencia,
            'numero_contato_emergencia' => $this->numero_contato_emergencia,
            'telefone' => $this->telefone,
            'sexo' => $this->sexo,
            'cidade' => $this->cidade,
            'participante_igreja' => (bool) $this->participante_igreja,
            'qual_igreja' => $this->qual_igreja,
            'usa_medicamento' => (bool) $this->usa_medicamento,
            'qual_medicamento' => $this->qual_medicamento,
            //'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            //'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }
} 