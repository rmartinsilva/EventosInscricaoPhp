<?php

namespace App\Services;

use App\DTO\CreateAcessoDTO;
use App\DTO\UpdateAcessoDTO;
use App\Repositories\Contracts\AcessoRepositoryInterface;
use App\Repositories\Contracts\PaginationInterface;
use stdClass;

class AcessoService
{
    public function __construct(
        protected AcessoRepositoryInterface $repository
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

    public function new(CreateAcessoDTO $dto): stdClass
    {
        return $this->repository->new($dto);
    }

    public function update(UpdateAcessoDTO $dto): ?stdClass
    {
        return $this->repository->update($dto);
    }

    public function delete(string $id): void
    {
        $this->repository->delete($id);
    }

    public function findByKey(string $key): ?stdClass
    {
        return $this->repository->findByKey($key);
    }
}
