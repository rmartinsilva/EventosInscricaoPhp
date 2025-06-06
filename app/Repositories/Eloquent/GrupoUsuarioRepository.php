<?php

namespace App\Repositories\Eloquent;

use App\DTO\CreateGrupoUsuarioDTO;
use App\DTO\UpdateGrupoUsuarioDTO;
use App\Models\Grupo;
use App\Models\GrupoUsuario;
use App\Repositories\Contracts\GrupoUsuarioRepositoryInterface;
use App\Repositories\Contracts\PaginationInterface;
use App\Repositories\PaginationPresenter;
use App\Http\Util\UsinaWeb_Exception;
use stdClass;
use Exception;
use Illuminate\Database\QueryException;

class GrupoUsuarioRepository implements GrupoUsuarioRepositoryInterface
{
    public function __construct(
        protected GrupoUsuario $model,
        protected Grupo $grupoModel
    ) {}

    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface
    {
        try {
            $query = $this->model->query();

            if ($filter) {
                $query->where(function ($q) use ($filter) {
                    $q->where('grupo_id', 'like', "%{$filter}%")
                      ->orWhere('usuario_id', 'like', "%{$filter}%");
                });
            }

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
                    $q->where('usuario_id', '=', "{$filter}");
                });
            }

            return $query->get();
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }

    public function findOne(string $id): ?stdClass
    {
        try {
            $grupoUsuario = $this->model->find($id);
            return $grupoUsuario ? (object) $grupoUsuario->toArray() : null;
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }

    public function new(CreateGrupoUsuarioDTO $dto): stdClass
    {
        try {
            $grupoUsuario = $this->model->create((array) $dto);
            return (object) $grupoUsuario->toArray();
        } catch (QueryException $ex) {
            // Captura específica para erros de banco de dados
            throw new UsinaWeb_Exception($ex);
        } catch (Exception $ex) {
            // Captura genérica para outros erros
            throw new UsinaWeb_Exception($ex);
        }
    }

    public function update(UpdateGrupoUsuarioDTO $dto): ?stdClass
    {
        try {
            if (!$grupoUsuario = $this->model->find($dto->id)) {
                return null;
            }

            $grupoUsuario->update((array) $dto);
            return (object) $grupoUsuario->toArray();
        } catch (QueryException $ex) {
            // Captura específica para erros de banco de dados
            throw new UsinaWeb_Exception($ex);
        } catch (Exception $ex) {
            // Captura genérica para outros erros
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

    public function getGruposDisponiveis(string $usuario_id)
    {
        try {

            // Iniciar a query para selecionar todos os grupos
            $query = $this->grupoModel->query();
            
            // Adicionar join para excluir grupos que já estão associados ao usuário
            $query->whereNotExists(function ($query) use ($usuario_id) {
                $query->select(\DB::raw(1))
                      ->from('grupo_usuario')
                      ->whereRaw('grupo_usuario.grupo_id = grupos.id')
                      ->where('grupo_usuario.usuario_id', $usuario_id);
            });
            // Ordenar pelo campo de descrição
            $query->orderBy('descricao');
            
            // Retornar o resultado
            return $query->get();
            
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }
}