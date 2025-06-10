<?php

namespace App\Http\Resources\Object;

use stdClass;

// Não estende JsonResource
class ConfiguracaoObjectResource
{
    public stdClass $objeto;

    // Recebe stdClass no construtor
    public function __construct(stdClass $objeto) {
        $this->objeto = $objeto;
    }

    // Método toObject() para retornar o array formatado
    public function toObject(): array
    {
        // Acessa propriedades do stdClass, adaptado para a Configuracao
        return [
            'id' => $this->objeto->id ?? null,
            'descricao_api' => $this->objeto->descricao_api ?? null,
            'chave_api' => $this->objeto->chave_api ?? null, // Omitido devido ao $hidden no Model
            'token_api' => $this->objeto->token_api ?? null,
            'webhooksecret' => $this->objeto->webhooksecret ?? null,
            'notificationurl' => $this->objeto->notificationurl ?? null,
           
        ];
    }
}
