<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {


          // Retorna a estrutura genérica desejada
          return [
            'id' => $this->id ?? null,
            'name' => $this->name ?? null, 
            'login' => $this->login ?? null,
        ];
    }

}
