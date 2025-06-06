<?php

namespace App\Repositories\Eloquent;

use App\DTO\CreateUsuarioDTO;
use App\DTO\UpdateUsuarioDTO;
use App\Http\Util\UsinaWeb_Exception;
use App\Http\Util\UsinaWeb_Exceptipon;
use App\Models\Usuario as Model;
use App\Repositories\Contracts\UsuarioRepositoryInterface;
use App\Repositories\PaginationPresenter;
use App\Repositories\Contracts\PaginationInterface;
use Exception;
use Illuminate\Support\Facades\Hash;
use stdClass;

class UsuarioRepository implements UsuarioRepositoryInterface
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
                    $query->where('name', 'like', "%{$filter}%");
                $query->orWhere('login', 'like', "%{$filter}%");
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
                    $query->where('name', 'like', "%{$filter}%");
                $query->orWhere('login', 'like', "%{$filter}%");
            })
            ->get();
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }

    public function findOne(string $id): ?stdClass
    {
        try {   
            $usuario = $this->model->find($id);
            if (!$usuario) {
                return null;
            }
            return (object) $usuario->toArray();
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

    public function new(CreateUsuarioDTO $dto): stdClass
    {
        try {
            $data = (array) $dto;
            $data['password'] = Hash::make($data['password']); // Hash da senha
            $usuario = $this->model->create($data);

            return (object) $usuario->toArray();
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }

    public function update(UpdateUsuarioDTO $dto): ?stdClass
    {
        try {
            if (!$usuario = $this->model->find($dto->id)) {
                return null;
            }

            $data = (array) $dto;
            // Só atualiza e faz hash da senha se ela for fornecida
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']); // Não atualiza a senha se estiver vazia
            }

            $usuario->update($data);

            return (object) $usuario->toArray();
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }

    public function findByLogin(string $login): ?stdClass
    {
        try {
            $usuario = $this->model->where('login', $login)->first();
            if (!$usuario) {
                return null;
            }
            return (object) $usuario->toArray();
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }
}
