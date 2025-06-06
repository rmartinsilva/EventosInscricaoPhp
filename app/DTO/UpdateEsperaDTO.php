<?php

namespace App\DTO;

use Illuminate\Http\Request;

class UpdateEsperaDTO
{
    public function __construct(
        public string $codigo,
        public ?string $evento_codigo = null,
        public ?string $participante_codigo = null
    ) {}

    public static function makeFromRequest(Request $request, string $codigo): self
    {
        return new self(
            codigo: $codigo,
            evento_codigo: $request->input('evento.codigo') ?? $request->input('evento_codigo'),
            participante_codigo: $request->input('participante.codigo') ?? $request->input('participante_codigo'),
        );
    }
} 