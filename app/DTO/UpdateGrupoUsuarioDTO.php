<?php

namespace App\DTO;

class UpdateGrupoUsuarioDTO
{
    public function __construct(
        public string $id,
        public string $grupo_id,
        public string $usuario_id
    ) {}

    public static function makeFromRequest($request, string $id): self
    {
        return new self(
            id: $id,
            grupo_id: $request->grupo['id'],
            usuario_id: $request->usuario['id']
        );
    }
} 