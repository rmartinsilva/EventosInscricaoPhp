<?php

namespace App\DTO;

use Illuminate\Http\Request;

class UpdateAcessoDTO
{
    public function __construct(
        public string $id,
        public string $descricao,
        public ?string $menu = null,
        public string $key
    ) {}

    public static function makeFromRequest(Request $request, string $id): self
    {
        return new self(
            $id,
            $request->descricao,
            $request->menu,
            $request->key
        );
    }
}
