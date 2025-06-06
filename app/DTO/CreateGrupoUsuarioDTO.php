<?php

namespace App\DTO;

class CreateGrupoUsuarioDTO
{
    public function __construct(
        public string $grupo_id,
        public string $usuario_id
    ) {}

    public static function makeFromRequest($request): self
    {
        return new self(
            grupo_id: $request->grupo['id'],
            usuario_id: $request->usuario['id']
        );
    }
} 