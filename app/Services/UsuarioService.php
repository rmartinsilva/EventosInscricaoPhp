<?php

namespace App\Services;

use App\DTO\CreateUsuarioDTO;
use App\DTO\UpdateUsuarioDTO;
use App\Repositories\Contracts\UsuarioRepositoryInterface;
use App\Repositories\Contracts\PaginationInterface;
use stdClass;

class UsuarioService
{
    public function __construct(
        protected UsuarioRepositoryInterface $repository
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

    public function new(CreateUsuarioDTO $dto): stdClass
    {
        // Aqui poderíamos adicionar lógica extra antes/depois de criar,
        // como validações de negócio, disparo de eventos, etc.
        return $this->repository->new($dto);
    }

    public function update(UpdateUsuarioDTO $dto): ?stdClass
    {
        // Lógica extra antes/depois de atualizar...
        return $this->repository->update($dto);
    }

    public function delete(string $id): void
    {
        // Lógica extra antes/depois de deletar...
        $this->repository->delete($id);
    }

    public function findByLogin(string $login): ?stdClass
    {
        return $this->repository->findByLogin($login);
    }

    /**
     * Verifica se um login já existe no sistema.
     */
    public function loginExists(string $login): bool
    {
        return $this->repository->findByLogin($login) !== null;
    }
}
