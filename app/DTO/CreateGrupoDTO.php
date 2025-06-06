<?php

namespace App\DTO;

use Illuminate\Http\Request;

class CreateGrupoDTO
{
    public function __construct(
        public string $descricao
    ) {}

    public static function makeFromRequest(Request $request): self
    {
        return new self(
            $request->descricao
        );
    }
}
