<?php

namespace App\DTO;

use Illuminate\Http\Request;

class CreateUsuarioDTO
{
    public function __construct(
        public string $name,
        public string $login,
        public string $password
    ) {}

    public static function makeFromRequest(Request $request): self
    {
        return new self(
            $request->name,
            $request->login,
            $request->password
        );
    }
}
