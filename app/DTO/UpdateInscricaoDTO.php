<?php

namespace App\DTO;

use App\Http\Requests\UpdateInscricaoRequest; // Será criado
use Illuminate\Http\Request; // Adicionado para o makeFromRequest genérico

class UpdateInscricaoDTO
{
    public function __construct(
        public string $codigo, // ID da inscrição a ser atualizada
        public ?string $evento_codigo = null,
        public ?string $participante_codigo = null,
        public ?string $data = null,
        public ?string $forma_pagamento = null,
        public ?string $status = null, // String porque pode ser 'P', 'C', etc.
        public ?bool $cortesia = null // Booleano pode ser null para não alterar
    ) {}

    public static function makeFromRequest(Request $request, string $codigo): self // Alterado para Request genérico
    {
        return new self(
            codigo: $codigo,
            evento_codigo: $request->evento['codigo'],
            participante_codigo: $request->participante['codigo'],
            data: $request->input('data'),
            forma_pagamento: $request->input('forma_pagamento'),
            status: $request->input('status'),
            // Para o booleano, só passamos se explicitamente enviado, senão não atualiza
            // ou usamos array_key_exists se quisermos permitir enviar null explicitamente para limpar o campo.
            // Por simplicidade, se não enviado, não atualiza.
            // Se enviado como string "true"/"false", converte. Se enviado como null, será null.
            cortesia: $request->has('cortesia') ? filter_var($request->input('cortesia'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null
        );
    }

    // Se precisar de um método específico para UpdateInscricaoRequest quando ele for criado:
    /*
    public static function makeFromUpdateRequest(UpdateInscricaoRequest $request, string $codigo): self
    {
        return new self(
            codigo: $codigo,
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