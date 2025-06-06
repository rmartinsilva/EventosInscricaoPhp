<?php

namespace App\Repositories\Contracts;

use App\DTO\CreateGrupoUsuarioDTO;
use App\DTO\UpdateGrupoUsuarioDTO;
use App\Repositories\Contracts\PaginationInterface;
use stdClass;

interface GrupoUsuarioRepositoryInterface
{
    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface;
    public function getAll(string $filter = null);
    public function findOne(string $id): ?stdClass;
    public function new(CreateGrupoUsuarioDTO $dto): stdClass;
    public function update(UpdateGrupoUsuarioDTO $dto): ?stdClass;
    public function delete(string $id): void;
    public function getGruposDisponiveis(string $usuario_id);
} 