<?php

namespace App\Http\Resources\Object;

use stdClass;

class UsuarioObjectResource
{
    public stdClass $objeto;

    public function __construct(stdClass $objeto) {
        $this->objeto = $objeto;
    }

    public function toObject(): array
    {
        // Retorna a estrutura genérica desejada
        return [
            'id' => $this->objeto->id ?? null,
            'name' => $this->objeto->name ?? null, 
            'login' => $this->objeto->login ?? null,
            // Adicionar grupos se necessário?
            // 'grupos' => GrupoResource::collection($this->whenLoaded('grupos')),
        ];
    }
}
