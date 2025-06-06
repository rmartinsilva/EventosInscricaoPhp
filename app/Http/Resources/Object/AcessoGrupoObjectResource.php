<?php

namespace App\Http\Resources\Object;

use stdClass;
// If you need to include related Acesso or Grupo details, you might import their ObjectResources:
// use App\Http\Resources\Object\AcessoObjectResource;
// use App\Http\Resources\Object\GrupoObjectResource;

class AcessoGrupoObjectResource
{
    public stdClass $objeto;

    public function __construct(stdClass $objeto)
    {
        $this->objeto = $objeto;
    }

    public function toObject(): array
    {
        $data = [
            'id' => $this->objeto->id ?? null,
            //'acesso_id' => $this->objeto->acesso_id ?? null,
            //'grupo_id' => $this->objeto->grupo_id ?? null,
            "acesso"=> [
                "id" => $this->objeto->acesso_id ?? null
             ],
             "grupo"=> [
                "id" => $this->objeto->grupo_id ?? null
             ],
            //'created_at' => $this->objeto->created_at ?? null,
            //'updated_at' => $this->objeto->updated_at ?? null,
        ];
        return $data;
    }
} 