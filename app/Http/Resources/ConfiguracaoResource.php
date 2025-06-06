<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConfiguracaoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Retorna todos os atributos do modelo, exceto os definidos em $hidden (chave_api)
        // return parent::toArray($request);
        
        // Ou define explicitamente os campos (mais controle)
         return [
            'id' => $this->id,
            'descricao_api' => $this->descricao_api,
             'chave_api' => $this->chave_api, // Omitido devido ao $hidden no Model
            //'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            //'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
