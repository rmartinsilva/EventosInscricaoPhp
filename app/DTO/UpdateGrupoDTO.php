<?php

namespace App\DTO;

use Illuminate\Http\Request;

class UpdateGrupoDTO
{
    public function __construct(
        public string $id,
        public string $descricao
    ) {}

    public static function makeFromRequest(Request $request, string $id): self
    {
        return new self(
            $id,
            $request->descricao
        );
    }
}
