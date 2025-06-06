<?php

namespace App\DTO;

use Illuminate\Http\Request;

class CreateAcessoDTO
{
    public function __construct(
        public string $descricao,
        public ?string $menu = null,
        public string $key
    ) {}

    public static function makeFromRequest(Request $request): self
    {
        return new self(
            $request->descricao,
            $request->menu,
            $request->key
        );
    }
}
