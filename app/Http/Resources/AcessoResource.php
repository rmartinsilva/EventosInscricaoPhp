<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcessoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Segue o padrão de UsuarioResource
        return [
            'id' => $this->id ?? null,
            'descricao' => $this->descricao ?? null,
            'menu' => $this->menu ?? null,
            'key' => $this->key ?? null, // Mapeia 'key' para 'chave'
            // Timestamps e outras informações são omitidas para um retorno mais limpo
        ];
    }
}
