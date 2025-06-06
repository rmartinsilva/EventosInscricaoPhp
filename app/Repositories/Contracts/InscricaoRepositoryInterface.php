<?php

namespace App\Repositories\Contracts;

use App\DTO\CreateInscricaoDTO;
use App\DTO\UpdateInscricaoDTO;
use App\Repositories\Contracts\PaginationInterface; // Já deve existir
use stdClass;
use Illuminate\Database\Eloquent\Collection; // Para getAll se for retornar coleção Eloquent

interface InscricaoRepositoryInterface
{
    public function paginate(int $page = 1, int $totalPerPage = 15, ?string $filter, ?string $evento): PaginationInterface;

    /**
     * Retorna todos os registros, opcionalmente filtrados.
     * Pode retornar uma coleção Eloquent ou um array de stdClass.
     */
    public function getAll(?string $filter, ?string $evento);

    public function getByParticipanteEvento(string $participanteCodigo, string $eventoCodigo): ?stdClass;

    public function findOne(string $codigo): ?stdClass;

    public function new(CreateInscricaoDTO $dto): stdClass;

    public function update(UpdateInscricaoDTO $dto): ?stdClass;

    public function delete(string $codigo): void;

    /**
     * Busca inscrições existentes para um determinado evento e participante.
     *
     * @param string $eventoCodigo
     * @param string $participanteCodigo
     * @return \Illuminate\Support\Collection Coleção de stdClass representando as inscrições.
     */
    public function findExisting(string $eventoCodigo, string $participanteCodigo): \Illuminate\Support\Collection;

    /**
     * Conta o número de inscrições pendentes (status 'P') para um evento específico.
     *
     * @param string $eventoCodigo
     * @return int
     */
    public function countPagaByEvento(string $eventoCodigo): int;

    /**
     * Conta o número de inscrições cortesias para um evento específico.
     *
     * @param string $eventoCodigo
     * @return int
     */
    public function countCortesiaByEvento(string $eventoCodigo): int;

    public function getAllByEvento(string $eventoCodigo, ?bool $filter);
} 