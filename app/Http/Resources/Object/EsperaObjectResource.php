<?php

namespace App\Http\Resources\Object;

use App\Http\Util\UsinaWeb_Datas;
use stdClass; // Para type hint do construtor

class EsperaObjectResource
{
    protected $data;

    public function __construct($resource)
    {
        // Espera um stdClass ou array do repositório/serviço
        $this->data = (object) $resource;
    }

    public function toObject(): stdClass
    {
        $usinaWeb_Datas = new UsinaWeb_Datas();
       // dd($this->data);
        return (object) [
            'codigo' => $this->data->codigo ?? null,
            'evento' => [
                'codigo' => $this->data->evento_codigo ?? null
            ],
            'participante' => [
                'codigo' => $this->data->participante_codigo ?? null
            ],
            'data_criacao' => $usinaWeb_Datas->formatDateString($this->data->created_at, UsinaWeb_Datas::formatoDataHora) ?? null,
            'data_atualizacao' => $usinaWeb_Datas->formatDateString($this->data->updated_at, UsinaWeb_Datas::formatoDataHora) ?? null,
        ];
        // Aqui você pode mapear ou transformar os dados do stdClass se necessário.
        // Por enquanto, retornaremos como está, assumindo que já tem o formato desejado.
        // Se o stdClass contiver 'evento' e 'participante' como objetos stdClass, eles serão incluídos.
        // Se forem apenas códigos, apenas os códigos serão retornados.
        //return $this->data;
    }
} 