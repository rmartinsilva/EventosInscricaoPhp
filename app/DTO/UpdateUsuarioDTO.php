<?php

namespace App\DTO;

use Illuminate\Http\Request;

class UpdateUsuarioDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $login,
        public ?string $password = null // Senha é opcional na atualização
    ) {}

    public static function makeFromRequest(Request $request, string $id): self
    {
        return new self(
            $id,
            $request->name,
            $request->login,
            $request->password ?? null
        );
    }
}
