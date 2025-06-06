<?php

namespace App\Repositories\Contracts;

use App\DTO\CreateParticipanteDTO;
use App\DTO\UpdateParticipanteDTO;
use stdClass;

interface ParticipanteRepositoryInterface
{
    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface;
    public function getAll(string $filter = null): array;
    public function findOne(string $id): ?stdClass;
    public function delete(string $id): void;
    public function new(CreateParticipanteDTO $dto): stdClass;
    public function update(UpdateParticipanteDTO $dto): ?stdClass;
    public function findByCpf(string $cpf): ?stdClass;
} 