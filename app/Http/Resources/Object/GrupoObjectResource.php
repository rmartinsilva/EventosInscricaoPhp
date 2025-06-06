<?php

namespace App\Http\Resources\Object;

use stdClass;

// Não estende JsonResource
class GrupoObjectResource
{
    public stdClass $objeto;

    // Recebe stdClass no construtor
    public function __construct(stdClass $objeto) {
        $this->objeto = $objeto;
    }

    // Método toObject() para retornar o array formatado
    public function toObject(): array
    {
        // Acessa propriedades do stdClass, adaptado para Grupo
        return [
            'id' => $this->objeto->id ?? null,
            'descricao' => $this->objeto->descricao ?? null,
            // Adicione outras propriedades ou relações de Grupo conforme necessário
            // Exemplo para relações (requer que o service retorne essas relações no stdClass):
            // 'usuarios' => isset($this->objeto->usuarios) ? UsuarioObjectResource::collection($this->objeto->usuarios) : [],
            // 'acessos' => isset($this->objeto->acessos) ? AcessoObjectResource::collection($this->objeto->acessos) : [],
        ];
    }
}
