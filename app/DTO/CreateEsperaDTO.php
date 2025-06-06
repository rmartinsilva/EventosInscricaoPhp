<?php

namespace App\DTO;

use Illuminate\Http\Request;

class CreateEsperaDTO
{
    public function __construct(
        public string $evento_codigo,
        public string $participante_codigo
    ) {}

    public static function makeFromRequest(Request $request): self
    {
        return new self(
            evento_codigo: $request->input('evento.codigo') ?? $request->input('evento_codigo'),
            participante_codigo: $request->input('participante.codigo') ?? $request->input('participante_codigo'),
        );
    }
} 