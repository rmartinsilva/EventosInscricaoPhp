<?php

namespace App\DTO;

use App\Http\Requests\UpdateConfiguracaoRequest;

class UpdateConfiguracaoDTO
{
    public function __construct(
        public string $id,
        public string $descricao_api,
        public string $chave_api,
    ) {}

    public static function makeFromRequest(UpdateConfiguracaoRequest $request, string $id): self
    {
        return new self(
            $id,
            $request->descricao_api,
            $request->chave_api ?? null, // Permite não enviar a chave na atualização
        );
    }
} 