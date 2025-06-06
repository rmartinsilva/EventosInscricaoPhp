<?php

namespace App\Http\Resources\Object;

use stdClass;
use Carbon\Carbon; // Para formatação de data se necessário

class InscricaoObjectResource
{
    public stdClass $objeto;

    public function __construct(stdClass $objeto)
    {
        $this->objeto = $objeto;
    }

    public function toObject(): array // Consistente com outros ObjectResources que retornam array
    {
        // As datas do stdClass vindo do repositório (toArray() do model) já são strings formatadas (ISO 8601)
        // Se precisar de formatação específica, pode usar Carbon aqui.
        $data = $this->objeto->data ?? null;
        if ($data && !($data instanceof Carbon)) {
            try {
                // Tenta converter para Carbon para garantir a formatação correta, se não for já uma string formatada
                $data = Carbon::parse($data)->toDateTimeString();
            } catch (\Exception $e) {
                // Mantém o valor original se não puder ser parseado
            }
        }

        $createdAt = $this->objeto->created_at ?? null;
        if ($createdAt && !($createdAt instanceof Carbon)) {
            try {
                $createdAt = Carbon::parse($createdAt)->toDateTimeString();
            } catch (\Exception $e) {}
        }

        $updatedAt = $this->objeto->updated_at ?? null;
        if ($updatedAt && !($updatedAt instanceof Carbon)) {
            try {
                $updatedAt = Carbon::parse($updatedAt)->toDateTimeString();
            } catch (\Exception $e) {}
        }

        return [
            'codigo' => $this->objeto->codigo ?? null,
            // Informações do evento e participante (apenas códigos, como em AcessoGrupoObjectResource)
            'evento' => [
                'codigo' => $this->objeto->evento_codigo ?? null,
                // Se o service/repository começar a retornar o objeto evento/participante aninhado no stdClass:
                // 'descricao' => $this->objeto->evento->descricao ?? null,
            ],
            'participante' => [
                'codigo' => $this->objeto->participante_codigo ?? null,
                // 'nome' => $this->objeto->participante->nome ?? null,
            ],
            'data' => $data,
            'forma_pagamento' => $this->objeto->forma_pagamento ?? null,
            'cortesia' => isset($this->objeto->cortesia) ? (bool) $this->objeto->cortesia : null,
            'status' => $this->objeto->status ?? null,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }
} 