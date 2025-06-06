<?php

namespace App\Http\Resources\Object;

use App\Http\Util\UsinaWeb_Datas;
use Illuminate\Http\Request;

class EventoObjectResource // Não estende JsonResource, é um formatador simples
{
    protected $data;

    public function __construct($resource)
    {
        // Espera um stdClass ou array do repositório/serviço
        $this->data = (object) $resource;
    }

    /**
     * Transforma o recurso em um objeto padrão para a resposta da API.
     *
     * @return object
     */
    public function toObject(): object
    {
        $usinaWeb_Datas = new UsinaWeb_Datas();
        return (object) [
            'codigo' => $this->data->codigo ?? null,
            'descricao' => $this->data->descricao ?? null,
            'data' =>   $usinaWeb_Datas->formatDateString($this->data->data, UsinaWeb_Datas::formatoDataSimples) ?? null,
            'data_inicio_inscricoes' => $usinaWeb_Datas->formatDateString($this->data->data_inicio_inscricoes, UsinaWeb_Datas::formatoDataHora) ?? null,
            'data_final_inscricoes' => $usinaWeb_Datas->formatDateString($this->data->data_final_inscricoes, UsinaWeb_Datas::formatoDataHora)    ?? null,
            'numero_inscricoes' => (int) ($this->data->numero_inscricoes ?? 0),
            'cortesias' => (bool) ($this->data->cortesias ?? false),
            'numero_cortesia' => isset($this->data->numero_cortesia) ? (int)$this->data->numero_cortesia : null,
            'link_obrigado' => $this->data->link_obrigado ?? null,
            'url' => $this->data->url ?? null,
            'valor' => isset($this->data->valor) ? $this->data->valor : null,
            //'valor' => isset($this->data->valor) ? Money::USD($this->data->valor,true) : null,
            'created_at' => $this->data->created_at ?? null,
            'updated_at' => $this->data->updated_at ?? null,
        ];
    }
} 