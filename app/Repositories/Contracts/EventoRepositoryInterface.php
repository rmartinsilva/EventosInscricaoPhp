<?php

namespace App\Repositories\Contracts;

use App\DTO\CreateEventoDTO;
use App\DTO\UpdateEventoDTO;
use stdClass;

interface EventoRepositoryInterface
{
    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface;
    public function getAll(?string $filter = null): array;
    public function findOne(string $id): ?stdClass;
    public function delete(string $id): void;
    public function new(CreateEventoDTO $dto): stdClass;
    public function update(UpdateEventoDTO $dto): ?stdClass;
    public function findByCodigo(string $codigo): ?stdClass;
    public function findByUrl(string $url): ?stdClass;
    public function getAllAtivos();
} 