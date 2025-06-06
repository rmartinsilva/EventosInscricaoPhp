<?php

namespace App\Repositories\Eloquent;

use App\DTO\CreateGrupoDTO;
use App\DTO\UpdateGrupoDTO;
use App\Http\Util\UsinaWeb_Exception;
use App\Http\Util\UsinaWeb_Exceptipon;
use App\Models\Grupo as Model;
use App\Repositories\Contracts\GrupoRepositoryInterface;
use App\Repositories\PaginationPresenter;
use App\Repositories\Contracts\PaginationInterface;
use Exception;
use stdClass;

class GrupoRepository implements GrupoRepositoryInterface
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

    public function delete(string $id): void
    {
        try {
            $this->model->findOrFail($id)->delete();
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }

    public function new(CreateGrupoDTO $dto): stdClass
    {
        try {
            $grupo = $this->model->create((array) $dto);
            return (object) $grupo->toArray();
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }

    public function update(UpdateGrupoDTO $dto): ?stdClass
    {
        try {
            if (!$grupo = $this->model->find($dto->id)) {
                return null;
            }

            $grupo->update((array) $dto);

            return (object) $grupo->toArray();
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }
}
