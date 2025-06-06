<?php

namespace App\Http\Resources\Object;

use stdClass;


class GrupoUsuarioObjectResource
{

    public stdClass $objeto;

    // Recebe stdClass no construtor
    public function __construct(stdClass $objeto) {
        $this->objeto = $objeto;
    }

    
    public function toObject(): array
    {
        return [
            'id' => $this->objeto->id,
            "grupo"=> [
                "id" => $this->objeto->grupo_id
            ],
            "usuario"=> [
                "id" => $this->objeto->usuario_id
            ],
            'created_at' => $this->objeto->created_at,
            'updated_at' => $this->objeto->updated_at,
        ];
    }
} 