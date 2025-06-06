<?php

namespace App\Repositories\Eloquent;

use App\DTO\CreateAcessoDTO;
use App\DTO\UpdateAcessoDTO;
use App\Http\Util\UsinaWeb_Exception;
use App\Http\Util\UsinaWeb_Exceptipon;
use App\Models\Acesso as Model;
use App\Repositories\Contracts\AcessoRepositoryInterface;
use App\Repositories\PaginationPresenter;
use App\Repositories\Contracts\PaginationInterface;
use Exception;
use stdClass;

class AcessoRepository implements AcessoRepositoryInterface
{
    public function __construct(
        protected Model $model
    ) {
    }

    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface
    {

        try {
            $result = $this->model
                ->when($filter, function ($query) use ($filter) {
                    $query->where('descricao', 'like', "%{$filter}%");
                    $query->orWhere('key', 'like', "%{$filter}%");
                    $query->orWhere('menu', 'like', "%{$filter}%");
                })
            ->paginate($totalPerPage, ['*'], 'page', $page);

            return new PaginationPresenter($result);
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
                $query->orWhere('key', 'like', "%{$filter}%");
                $query->orWhere('menu', 'like', "%{$filter}%");
            })
            ->get();
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }

    public function findOne(string $id): ?stdClass
    {
        try {
            $acesso = $this->model->find($id);
            if (!$acesso) {
                return null;
        }
            return (object) $acesso->toArray();
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }

    public function delete(string $id): void
    {
        try {
            $this->model->findOrFail($id)->delete();
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }

    public function new(CreateAcessoDTO $dto): stdClass
    {
        try {
            $acesso = $this->model->create((array) $dto);
            return (object) $acesso->toArray();
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }

    public function update(UpdateAcessoDTO $dto): ?stdClass
    {
        try {
            if (!$acesso = $this->model->find($dto->id)) {
                return null;
            }

            $acesso->update((array) $dto);

            return (object) $acesso->toArray();
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }

    public function findByKey(string $key): ?stdClass
    {

        $acesso = $this->model->where('key', $key)->first();
        if (!$acesso) {
            return null;
        }
        return (object) $acesso->toArray();

    }
}
