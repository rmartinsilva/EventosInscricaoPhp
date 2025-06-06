<?php

namespace App\Repositories\Eloquent;

use App\Http\Util\UsinaWeb_Exception;
use App\Models\PagamentoPendente as Model;
use App\Repositories\Contracts\PagamentoPendenteRepositoryInterface;
use Exception;
use stdClass;

class PagamentoPendenteRepository implements PagamentoPendenteRepositoryInterface
{
    public function __construct(
        protected Model $model
    ) {}

    public function getById(int $idPagamento)
    {
        try {
            $pagamento = $this->model->where('id_pagamento_mp', $idPagamento)->first();
            if (!$pagamento) {
                return null;
            }
            return $pagamento;
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }




    public function getAll(string $filter = null)
    {
        try {
            return $this->model
                ->when($filter, function ($query) use ($filter) {
                    $query->where('descricao', 'like', "%{$filter}%");
                })
                ->get()
            ;
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }

    public function findOne(string $id): ?stdClass
    {
        try {
            $grupo = $this->model->find($id);
            if (!$grupo) {
                return null;
            }
            return (object) $grupo->toArray();
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }
}
