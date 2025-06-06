<?php

namespace App\Services;

use App\DTO\CreateInscricaoDTO;
use App\DTO\UpdateInscricaoDTO;
use App\Repositories\Contracts\InscricaoRepositoryInterface;
use App\Repositories\Contracts\PaginationInterface;
use App\Http\Util\UsinaWeb_Exception; // Para tratamento de exceções customizadas
use Illuminate\Support\Facades\Log; // Adicionado para logar
use stdClass;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class InscricaoService
{
    public function __construct(
        protected InscricaoRepositoryInterface $repository
    ) {}

    public function paginate(int $page = 1, int $totalPerPage = 15, ?string $filter, ?string $evento): PaginationInterface
    {
        return $this->repository->paginate(
            page: $page,
            totalPerPage: $totalPerPage,
            filter: $filter,
            evento: $evento
        );
    }


    public function getByParticipanteEvento(string $participanteCodigo, string $eventoCodigo): ?stdClass
    {
        return $this->repository->getByParticipanteEvento($participanteCodigo, $eventoCodigo);
    }

    public function getAll(?string $filter, ?string $evento)
    {
        return $this->repository->getAll($filter, $evento);
    }

    public function findOne(string $codigo): ?stdClass
    {
        return $this->repository->findOne($codigo);
    }

    public function new(CreateInscricaoDTO $dto): stdClass
    {
        // Validação da regra de negócio: não permitir participante duplicado no mesmo evento,
        // a menos que a inscrição existente esteja cancelada (status 'C').
        $existingInscricoes = $this->repository->findExisting(
            eventoCodigo: $dto->evento_codigo,
            participanteCodigo: $dto->participante_codigo
        );

        if (!$existingInscricoes->isEmpty()) {
            foreach ($existingInscricoes as $inscricao) {
                // $inscricao é um stdClass aqui, conforme retornado pelo repositório
                if (isset($inscricao->status) && strtoupper($inscricao->status) !== 'C') { 
                    throw new UsinaWeb_Exception("Este participante já possui uma inscrição ativa ou pendente para este evento.", 409); // 409 Conflict
                }
            }
        }

        // Adicionar validações de negócio aqui se necessário   
        // Ex: Verificar se o evento permite novas inscrições, se o participante já está inscrito, etc.
        try {
            // Log::debug('InscricaoService@new: Tentando criar inscrição com DTO', [
            //     'evento_codigo' => $dto->evento_codigo,
            //     'participante_codigo' => $dto->participante_codigo,
            //     'data' => $dto->data,
            //     'forma_pagamento' => $dto->forma_pagamento,
            //     'status' => $dto->status,
            //     'cortesia' => $dto->cortesia
            // ]);
            return $this->repository->new($dto);
        } catch (UsinaWeb_Exception $e) {
            // Se já for uma exceção customizada, apenas re-lança e loga
            // Log::error('InscricaoService@new: UsinaWeb_Exception ao criar inscrição', [
            //     'exception_class' => get_class($e),
            //     'error_message' => $e->getMessage(),
            //     'error_code' => $e->getCode(),
            //     'dto' => (array) $dto
            // ]);
            throw $e;
        } catch (Exception $e) {
            // Para outras exceções, loga detalhadamente e lança uma UsinaWeb_Exception
            // Log::error('InscricaoService@new: Exceção genérica ao criar inscrição', [
            //     'exception_class' => get_class($e),
            //     'original_error_message' => $e->getMessage(),
            //     'original_error_code' => $e->getCode(),
            //     'dto' => (array) $dto,
            //     'trace' => $e->getTraceAsString() // Logar o trace completo para depuração profunda
            // ]);
            throw new UsinaWeb_Exception("Erro de aplicação ao tentar processar a inscrição: " . $e->getMessage(), 500, $e);
        }
    }

    public function update(UpdateInscricaoDTO $dto): ?stdClass
    {
        // Adicionar validações de negócio aqui se necessário
        // try {
        $updatedInscricao = $this->repository->update($dto);
        if (!$updatedInscricao) {
            // O repositório pode retornar null se não encontrar, o que pode ser ok
            // ou podemos lançar ModelNotFoundException aqui se o service sempre espera que exista
            // Depende da política do controller. Por ora, deixo o controller tratar null.
        }
        return $updatedInscricao;
        // } catch (ModelNotFoundException $e) {
        //     throw $e; // Re-lançar para o controller
        // } catch (Exception $e) {
        //     throw new UsinaWeb_Exception("Erro de negócio ao atualizar inscrição: " . $e->getMessage());
        // }
    }

    public function delete(string $codigo): void
    {
        // Adicionar validações de negócio aqui se necessário (ex: só pode deletar se status for X)
        // try {
        $this->repository->delete($codigo);
        // } catch (ModelNotFoundException $e) {
        //     throw $e; // Re-lançar para o controller
        // } catch (Exception $e) {
        //     throw new UsinaWeb_Exception("Erro de negócio ao deletar inscrição: " . $e->getMessage());
        // }
    }

    public function countPagaByEvento(string $eventoCodigo): int
    {
        try {
            return $this->repository->countPagaByEvento($eventoCodigo);
        } catch (Exception $e) {
            // Log::error("InscricaoService@countPendentesByEvento: Exceção ao contar inscrições pendentes para o evento {$eventoCodigo}", [
            //     'exception_class' => get_class($e),
            //     'error_message' => $e->getMessage(),
            //     'error_code' => $e->getCode(),
            //     'trace' => $e->getTraceAsString()
            // ]);
            // Lançar uma exceção mais genérica ou específica do domínio, se apropriado
            throw new UsinaWeb_Exception("Erro ao buscar contagem de inscrições pagas para o evento {$eventoCodigo}: " . $e->getMessage(), 500, $e);
        }
    }

    public function countCortesiaByEvento(string $eventoCodigo): int
    {
        return $this->repository->countCortesiaByEvento($eventoCodigo);
    }

    public function getAllByEvento(string $eventoCodigo, ?bool $filter)
    {
        return $this->repository->getAllByEvento($eventoCodigo, $filter);
    }
} 