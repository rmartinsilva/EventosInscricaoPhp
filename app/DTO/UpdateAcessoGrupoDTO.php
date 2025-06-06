<?php

namespace App\DTO;

use Illuminate\Http\Request; // Assuming Request might be used, adjust if validated data array is passed

class UpdateAcessoGrupoDTO
{
    public function __construct(
        public string $id,       // ID of the acesso_grupo record itself
        public string $acesso_id,
        public string $grupo_id
    ) {}
   
    public static function makeFromRequest($request, string $id): self
    {
       
            return new self(
                id: $id,
                acesso_id: $request->acesso['id'],
                grupo_id: $request->grupo['id']
                
            );
       
    }
} 