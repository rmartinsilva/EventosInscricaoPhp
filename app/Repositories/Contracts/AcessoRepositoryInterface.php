<?php

namespace App\Repositories\Contracts;

use App\DTO\CreateAcessoDTO;
use App\DTO\UpdateAcessoDTO;
use stdClass;
use App\Repositories\Contracts\PaginationInterface;

interface AcessoRepositoryInterface
{
    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface;
    public function getAll(string $filter = null);
    public function findOne(string $id): ?stdClass;
    public function delete(string $id): void;
    public function new(CreateAcessoDTO $dto): stdClass;
    public function update(UpdateAcessoDTO $dto): ?stdClass;
    public function findByKey(string $key): ?stdClass; // Pode ser útil
}
