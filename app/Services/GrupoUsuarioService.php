<?php

namespace App\Services;

use App\DTO\CreateGrupoUsuarioDTO;
use App\DTO\UpdateGrupoUsuarioDTO;
use App\Repositories\Contracts\GrupoUsuarioRepositoryInterface;
use App\Repositories\Contracts\PaginationInterface;
use stdClass;

class GrupoUsuarioService
{
    public function __construct(
        protected GrupoUsuarioRepositoryInterface $repository
    ) {}

    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface
    {
        return $this->repository->paginate(
            page: $page,
            totalPerPage: $totalPerPage,
            filter: $filter,
        );
    }

    public function getAll(string $filter = null)
    {
        return $this->repository->getAll($filter);
    }

    public function findOne(string $id): ?stdClass
    {
        return $this->repository->findOne($id);
    }

    public function new(CreateGrupoUsuarioDTO $dto): stdClass
    {
        return $this->repository->new($dto);
    }

    public function update(UpdateGrupoUsuarioDTO $dto): ?stdClass
    {
        return $this->repository->update($dto);
    }

    public function delete(string $id): void
    {
        $this->repository->delete($id);
    }

    public function getGruposDisponiveis(string $usuario_id)
    {
        return $this->repository->getGruposDisponiveis($usuario_id);
    }
} 