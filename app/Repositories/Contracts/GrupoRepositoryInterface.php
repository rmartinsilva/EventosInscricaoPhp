<?php

namespace App\Repositories\Contracts;

use App\DTO\CreateGrupoDTO;
use App\DTO\UpdateGrupoDTO;
use stdClass;
use App\Repositories\Contracts\PaginationInterface;

interface GrupoRepositoryInterface
{
    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface;
    public function getAll(string $filter = null);
    public function findOne(string $id): ?stdClass;
    public function delete(string $id): void;
    public function new(CreateGrupoDTO $dto): stdClass;
    public function update(UpdateGrupoDTO $dto): ?stdClass;
    // Relacionamentos (opcional, pode estar no Service)
    // public function syncAcessos(string $id, array $acessos): void;
    // public function syncUsuarios(string $id, array $usuarios): void;
}
