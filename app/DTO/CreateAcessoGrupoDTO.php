<?php

namespace App\DTO;

class CreateAcessoGrupoDTO
{
    public function __construct(
        public string $acesso_id,
        public string $grupo_id
    ) {}

     public static function makeFromRequest($request): self
    {
        //dd($request['acesso']['id']);
            return new self(
                acesso_id: $request->acesso['id'],
                grupo_id: $request->grupo['id']
                
                
            );
        
    }
} 