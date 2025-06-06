<?php

namespace App\Http\Resources\Object;

use stdClass;

// Não estende JsonResource
class AcessoObjectResource
{
    public stdClass $objeto;

    // Recebe stdClass no construtor
    public function __construct(stdClass $objeto) {
        $this->objeto = $objeto;
    }

    // Método toObject() para retornar o array formatado
    public function toObject(): array
    {
        // Acessa propriedades do stdClass, adaptado para Acesso
        return [
            'id' => $this->objeto->id ?? null,
            'descricao' => $this->objeto->descricao ?? null,
            'menu' => $this->objeto->menu ?? null,
            'key' => $this->objeto->key ?? null, // Mapeia 'key' do model/stdClass para 'chave' na resposta
            // Adicione outras propriedades de Acesso conforme necessário
            // 'created_at' => $this->objeto->created_at ?? null, // Exemplo
            // 'updated_at' => $this->objeto->updated_at ?? null, // Exemplo
        ];
    }
}
