<?php

namespace App\Repositories\Eloquent;

use App\DTO\CreateAcessoGrupoDTO;
use App\DTO\UpdateAcessoGrupoDTO;
use App\Models\AcessoGrupo as Model;
use App\Models\Acesso;
use App\Repositories\Contracts\AcessoGrupoRepositoryInterface;
use App\Repositories\Contracts\PaginationInterface;
use App\Repositories\PaginationPresenter;
use App\Http\Util\UsinaWeb_Exception;
use stdClass;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AcessoGrupoRepository implements AcessoGrupoRepositoryInterface
{
    protected Model $model;
    protected Acesso $acessoModel;

    public function __construct(
        Model $model,
        Acesso $acessoModel
    ) {
        $this->model = $model;
        $this->acessoModel = $acessoModel;
    }

    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface
    {
        try {
            $query = $this->model->query();

            if ($filter) {                
                    // Fallback or more generic filter if needed
                    $query->where(function ($q) use ($filter) {
                        $q->where('grupo_id', '=', "%{$filter}%");
                    });                
            }

            // Optionally load relationships
            // $query->with(['acesso', 'grupo']); 

            $result = $query->paginate($totalPerPage, ['*'], 'page', $page);
            return new PaginationPresenter($result);
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }

    public function getAll(string $filter = null)
    {
        try {
            $query = $this->model->query();
           
            if ($filter) {
           
                $query->where(function ($q) use ($filter) {
                    $q->where('grupo_id', '=', "{$filter}");                    
                });
            }
            // Optionally load relationships
            // $query->with(['acesso', 'grupo']);
            return $query->get(); // Or convert to array of stdClass if preferred
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }

    public function findOne(string $id): ?stdClass
    {
        try {
            $record = $this->model->find($id);
            // Optionally load relationships if needed here
            // if ($record) $record->load(['acesso', 'grupo']);
            return $record ? (object) $record->toArray() : null;
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }

    public function new(CreateAcessoGrupoDTO $dto): stdClass
    {
        try {
            $data = (array) $dto;
            $record = $this->model->create($data);
            return (object) $record->toArray();
        } catch (QueryException $ex) {
            // Handle potential unique constraint violation (acesso_id, grupo_id)
            if ($ex->errorInfo[1] == 1062) { // MySQL duplicate entry
                 throw new UsinaWeb_Exception("Esta combinação de acesso e grupo já existe.");
            }
            throw new UsinaWeb_Exception($ex);
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }

    public function update(UpdateAcessoGrupoDTO $dto): ?stdClass
    {
        try {
            if (!$record = $this->model->find($dto->id)) {
                return null;
            }
            $data = (array) $dto;
            unset($data['id']); // Do not try to update the ID itself
            
            $record->update($data);
            return (object) $record->toArray();
        } catch (QueryException $ex) {
            if ($ex->errorInfo[1] == 1062) { // MySQL duplicate entry for unique constraint
                throw new UsinaWeb_Exception("Esta combinação de acesso e grupo já existe.");
           }
            throw new UsinaWeb_Exception($ex);
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

    public function findByGrupo(string $grupo_id)
    {
        try {
            $record = $this->model->where('grupo_id', $grupo_id);
            return $record->get();
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }

    public function getAcessosDisponiveisParaGrupo(string $grupo_id): Collection
    {
        try {
            $query = $this->acessoModel->query();

            $query->whereNotExists(function ($subQuery) use ($grupo_id) {
                $subQuery->select(DB::raw(1))
                         ->from('acesso_grupo')
                         ->whereRaw('acesso_grupo.acesso_id = acessos.id')
                         ->where('acesso_grupo.grupo_id', $grupo_id);
            });

            // Ordenar pela descrição do acesso, por exemplo
            $query->orderBy('descricao');

            return $query->get();
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }
} 