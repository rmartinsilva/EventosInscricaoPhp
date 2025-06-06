<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GrupoResource extends JsonResource
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
            // Timestamps e relações (usuarios, acessos) são omitidas por padrão
            // Para incluir relações, use:
            // 'usuarios' => UsuarioResource::collection($this->whenLoaded('usuarios')),
            // 'acessos' => AcessoResource::collection($this->whenLoaded('acessos')),
        ];
    }
}
