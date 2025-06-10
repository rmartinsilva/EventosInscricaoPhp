<?php

namespace App\DTO;

use App\Http\Requests\UpdateConfiguracaoRequest;

class UpdateConfiguracaoDTO
{
    public function __construct(
        public string $id,
        public string $descricao_api,
        public ?string $chave_api,
        public ?string $token_api,
        public ?string $webhooksecret,
        public ?string $notificationurl,
    ) {}

    public static function makeFromRequest(UpdateConfiguracaoRequest $request, string $id): self
    {
        return new self(
            $id,
            $request->descricao_api,
            $request->chave_api,
            $request->token_api,
            $request->webhooksecret,
            $request->notificationurl,
        );
    }
} 