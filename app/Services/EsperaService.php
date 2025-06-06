<?php

namespace App\Services;

use App\DTO\CreateEsperaDTO;
use App\DTO\UpdateEsperaDTO;
use App\Repositories\Contracts\EsperaRepositoryInterface as EsperaRepository;
use App\Repositories\Contracts\PaginationInterface;
use App\Http\Util\UsinaWeb_Exception; 
use stdClass;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class EsperaService
{
    public function __construct(
        protected EsperaRepository $repository
    ) {}

    public function paginate(int $page = 1, int $totalPerPage = 15, ?string $filter = null, ?string $evento = null): PaginationInterface
    {
        return $this->repository->paginate(
            page: $page,
            totalPerPage: $totalPerPage,
            filter: $filter,
            evento: $evento
        );
    }

    public function getAll(?string $evento = null): Collection
    {
        return $this->repository->getAll(evento: $evento);
    }

    public function findOne(string $codigo): ?stdClass
    {
        return $this->repository->findOne($codigo);
    }

    public function new(CreateEsperaDTO $dto): ?stdClass
    {
        // Verifica se já existe um item na lista de espera para este participante e evento
        $existingEspera = $this->repository->getByParticipanteEvento($dto->participante_codigo, $dto->evento_codigo);
        if ($existingEspera) {
            // Pode ser uma UsinaWeb_Exception ou um retorno que indique a duplicidade
            throw new UsinaWeb_Exception("Este participante já está na lista de espera para este evento.", 409); // 409 Conflict
        }
        return $this->repository->new($dto);
    }

    public function update(UpdateEsperaDTO $dto): ?stdClass
    {
        // Opcional: Adicionar lógica de verificação de duplicidade se os códigos de evento/participante puderem ser alterados
        // e a combinação precise continuar única.
        return $this->repository->update($dto);
    }

    public function delete(string $codigo): void
    {
        $this->repository->delete($codigo);
    }

    public function getByParticipanteEvento(string $participanteCodigo, string $eventoCodigo): ?stdClass
    {
        return $this->repository->getByParticipanteEvento($participanteCodigo, $eventoCodigo);
    }
} 