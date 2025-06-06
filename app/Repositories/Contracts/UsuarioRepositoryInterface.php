<?php

namespace App\Repositories\Contracts;

use App\DTO\CreateUsuarioDTO;
use App\DTO\UpdateUsuarioDTO;
use stdClass;
use App\Repositories\Contracts\PaginationInterface;

interface UsuarioRepositoryInterface
{
    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface;
    public function getAll(string $filter = null);
    public function findOne(string $id): ?stdClass;
    public function delete(string $id): void;
    public function new(CreateUsuarioDTO $dto): stdClass;
    public function update(UpdateUsuarioDTO $dto): ?stdClass;
    public function findByLogin(string $login): ?stdClass; // Método útil para autenticação/verificação
}
