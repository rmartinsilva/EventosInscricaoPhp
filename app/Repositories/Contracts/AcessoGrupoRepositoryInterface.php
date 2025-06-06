<?php

namespace App\Repositories\Contracts;

use App\DTO\CreateAcessoGrupoDTO;
use App\DTO\UpdateAcessoGrupoDTO;
use App\Repositories\Contracts\PaginationInterface;
use stdClass;

interface AcessoGrupoRepositoryInterface
{
    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface;
    public function getAll(string $filter = null); // Usually returns a collection/array of stdClass or Models
    public function findOne(string $id): ?stdClass;
    public function new(CreateAcessoGrupoDTO $dto): stdClass;
    public function update(UpdateAcessoGrupoDTO $dto): ?stdClass;
    public function delete(string $id): void;
    public function findByGrupo(string $grupo_id);
    public function getAcessosDisponiveisParaGrupo(string $grupo_id): \Illuminate\Database\Eloquent\Collection;
} 