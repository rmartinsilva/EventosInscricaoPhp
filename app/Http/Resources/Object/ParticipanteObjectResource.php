<?php

namespace App\Http\Resources\Object;

use App\Http\Util\UsinaWeb_Datas;
use stdClass;

class ParticipanteObjectResource
{
    protected $data;

    public function __construct($resource) // Recebe stdClass ou array
    {
        $this->data = (object) $resource;
    }

    public function toObject(): object
    {
        $usinaWeb_Datas = new UsinaWeb_Datas();
        return (object) [
            'codigo' => $this->data->codigo ?? null,
            'nome' => $this->data->nome ?? null,
            'cpf' => $this->data->cpf ?? null,
            'email' => $this->data->email ?? null,
            'data_nascimento' => $usinaWeb_Datas->formatDateString($this->data->data_nascimento, UsinaWeb_Datas::formatoDataSimples) ?? null,
            'nome_contato_emergencia' => $this->data->nome_contato_emergencia ?? null,
            'numero_contato_emergencia' => $this->data->numero_contato_emergencia ?? null,
            'telefone' => $this->data->telefone ?? null,
            'sexo' => $this->data->sexo ?? null,
            'cidade' => $this->data->cidade ?? null,
            'participante_igreja' => isset($this->data->participante_igreja) ? (bool) $this->data->participante_igreja : false,
            'qual_igreja' => $this->data->qual_igreja ?? null,
            'usa_medicamento' => isset($this->data->usa_medicamento) ? (bool) $this->data->usa_medicamento : false,
            'qual_medicamento' => $this->data->qual_medicamento ?? null,
            //'created_at' => $this->data->created_at ?? null,
            //'updated_at' => $this->data->updated_at ?? null,
        ];
    }
} 