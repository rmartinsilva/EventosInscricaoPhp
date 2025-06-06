<?php

namespace App\Repositories\Contracts;

use App\DTO\CreateConfiguracaoDTO;
use App\DTO\UpdateConfiguracaoDTO;
use stdClass;

interface ConfiguracaoRepositoryInterface
{
    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface;
    public function getAll(string $filter = null): array;
    public function findOne(string $id): ?stdClass;
    public function delete(string $id): void;
    public function new(CreateConfiguracaoDTO $dto): stdClass;
    public function update(UpdateConfiguracaoDTO $dto): ?stdClass;
    public function findByDescricao(string $descricao): ?stdClass;
} 