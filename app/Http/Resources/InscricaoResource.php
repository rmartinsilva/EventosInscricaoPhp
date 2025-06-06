<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InscricaoResource extends JsonResource
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
            'data' => $this->data ? ($this->data instanceof \Carbon\Carbon ? $this->data->toDateTimeString() : $this->data) : null,
            'forma_pagamento' => $this->forma_pagamento,
            'cortesia' => (bool) $this->cortesia,
            'status' => $this->status,
            
            // Relações (seguindo o padrão AcessoGrupoResource, mas incluindo nome/descrição)
            'evento' => [
                'codigo' => $this->evento_codigo,
                // Para carregar a descrição do evento, o model Inscricao precisa ter a relação evento()
                // e o controller/service deve carregar com with('evento')
                'descricao' => $this->whenLoaded('evento', function () {
                    return $this->evento->descricao; // Assumindo que Evento tem um campo 'descricao'
                }),
            ],
            'participante' => [
                'codigo' => $this->participante_codigo,
                'nome' => $this->whenLoaded('participante', function () {
                    return $this->participante->nome; // Assumindo que Participante tem um campo 'nome'
                }),
                'telefone' => $this->whenLoaded('participante', function () {
                    return $this->participante->telefone; // Assumindo que Participante tem um campo 'telefone'
                }),
            ],
            
            'created_at' => $this->created_at ? ($this->created_at instanceof \Carbon\Carbon ? $this->created_at->toDateTimeString() : $this->created_at) : null,
            'updated_at' => $this->updated_at ? ($this->updated_at instanceof \Carbon\Carbon ? $this->updated_at->toDateTimeString() : $this->updated_at) : null,
        ];
    }
} 