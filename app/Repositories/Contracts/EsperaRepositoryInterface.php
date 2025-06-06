<?php

namespace App\Repositories\Contracts;

use App\DTO\CreateEsperaDTO;
use App\DTO\UpdateEsperaDTO;
use stdClass;
use Illuminate\Support\Collection;

interface EsperaRepositoryInterface
{
    public function paginate(int $page = 1, int $totalPerPage = 15, ?string $filter, ?string $evento): PaginationInterface;
    public function getAll(?string $evento): Collection;
    public function findOne(string $codigo): ?stdClass;
    public function new(CreateEsperaDTO $dto): stdClass;
    public function update(UpdateEsperaDTO $dto): ?stdClass;
    public function delete(string $codigo): void;
    public function getByParticipanteEvento(string $participanteCodigo, string $eventoCodigo): ?stdClass;
} 