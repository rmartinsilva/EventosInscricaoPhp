<?php

namespace App\DTO;

use App\Http\Requests\StoreConfiguracaoRequest;

class CreateConfiguracaoDTO
{
    public function __construct(
        public string $descricao_api,
        public string $chave_api,
        public ?string $token_api,
        public ?string $webhooksecret,
        public ?string $notificationurl,
    ) {}

    public static function makeFromRequest(StoreConfiguracaoRequest $request): self
    {
        return new self(
            $request->descricao_api,
            $request->chave_api,
            $request->token_api,
            $request->webhooksecret,
            $request->notificationurl,
        );
    }
} 