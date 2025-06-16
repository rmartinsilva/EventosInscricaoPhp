<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Para listagens, podemos querer mostrar menos detalhes ou formatar diferente
        return [
            'codigo' => $this->codigo,
            'descricao' => $this->descricao,
            'data' => $this->data->format('Y-m-d'), // O cast no model já formata como Y-m-d
            'data_inicio_inscricoes' => $this->data_inicio_inscricoes ? $this->data_inicio_inscricoes->format('Y-m-d H:i:s') : null, // Formatado
            'data_final_inscricoes' => $this->data_final_inscricoes ? $this->data_final_inscricoes->format('Y-m-d H:i:s') : null, // Formatado
            'numero_inscricoes' => $this->numero_inscricoes,
            'cortesias' => $this->cortesias,
            'numero_cortesia' => $this->numero_cortesia,
            'url' => $this->url,
            //'valor' => Money::USD($this->valor,true),
            'valor' => $this->valor,
            // Omitindo numero_cortesia e link_obrigado na listagem padrão
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
