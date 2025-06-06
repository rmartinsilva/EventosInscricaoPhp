<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\EventoResource; // Para carregar dados do evento
use App\Http\Resources\ParticipanteResource; // Para carregar dados do participante
use App\Models\Espera; // Para type hint e whenLoaded

class EsperaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Espera $this */ // Ajuda o type hinter a entender que $this é um modelo Espera
        return [
            'codigo' => $this->codigo,
            'evento' => [
                'codigo' => $this->evento_codigo,
            ],
            'participante' => [
                'codigo' => $this->participante_codigo,
            ],
            //'evento' => new EventoResource($this->whenLoaded('evento')),
            //'participante' => new ParticipanteResource($this->whenLoaded('participante')),
            'data_criacao' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'data_atualizacao' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }
} 