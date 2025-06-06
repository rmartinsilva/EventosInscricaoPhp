<?php

namespace App\DTO;

use App\Http\Requests\StoreInscricaoRequest; // Será criado
use Illuminate\Http\Request; // Adicionado para o makeFromRequest genérico
use Illuminate\Support\Facades\Log; // Adicionado para logar

class CreateInscricaoDTO
{
    public function __construct(
        public string $evento_codigo,
        public string $participante_codigo,
        public string $data, // Mantido, mas será preenchido automaticamente
        public string $forma_pagamento,
        public string $status,
        public ?bool $cortesia = null
    ) {}

    public static function makeFromRequest(Request $request): self
    {
        $now = now('America/Sao_Paulo');
        return new self(
            evento_codigo: $request->input('evento.codigo'),
            participante_codigo: $request->input('participante.codigo'),
            data: $now->toDateTimeString(), // Define a data e hora atuais
            forma_pagamento: $request->input('forma_pagamento'),
            status: $request->input('status'),
            cortesia: $request->input('cortesia') ? (bool)$request->input('cortesia') : null
        );
    }

    // Se precisar de um método específico para StoreInscricaoRequest quando ele for criado:
    /*
    public static function makeFromStoreRequest(StoreInscricaoRequest $request): self
    {
        return new self(
            evento_codigo: $request->validated('evento_codigo'),
            participante_codigo: $request->validated('participante_codigo'),
            data: $request->validated('data'),
            forma_pagamento: $request->validated('forma_pagamento'),
            status: $request->validated('status'),
            cortesia: $request->validated('cortesia')
        );
    }
    */
} 