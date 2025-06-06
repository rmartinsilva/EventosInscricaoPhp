<?php

namespace App\Services;

use App\DTO\CreateEventoDTO;
use App\DTO\UpdateEventoDTO;
use App\Repositories\Contracts\EventoRepositoryInterface;
use App\Repositories\Contracts\PaginationInterface;
use App\Http\Util\UsinaWeb_Exception;
use stdClass;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Log; // Para logs, se necessário

class EventoService
{
    public function __construct(
        protected EventoRepositoryInterface $repository
    ) {}

    public function paginate(int $page = 1, int $totalPerPage = 15, ?string $filter = null): PaginationInterface
    {
        return $this->repository->paginate(
            page: $page,
            totalPerPage: $totalPerPage,
            filter: $filter
        );
    }

    public function getAll(?string $filter = null): array
    {
        return $this->repository->getAll($filter);
    }

    public function getAllAtivos()
    {
        return $this->repository->getAllAtivos();
    }

    public function findOne(string $id): ?stdClass
    {
        return $this->repository->findOne($id);
    }

    public function new(CreateEventoDTO $dto): stdClass
    {
        // Validações de unicidade (código e URL) já devem ser tratadas pelo FormRequest.
        // Aqui podemos adicionar lógicas de negócio mais complexas se necessário.

        // Ex: Verificar se data final >= data início
        if (strtotime($dto->data_final_inscricoes) < strtotime($dto->data_inicio_inscricoes)) {
            throw new UsinaWeb_Exception("A data final das inscrições não pode ser anterior à data de início.");
        }

        // Ex: Se tem cortesia, numero_cortesia deve ser > 0
        if ($dto->cortesias && (!$dto->numero_cortesia || $dto->numero_cortesia <= 0)) {
            throw new UsinaWeb_Exception("Se o evento oferece cortesias, o número de cortesias deve ser informado e maior que zero.");
        }
        // Se não tem cortesia, zerar o número para consistência
        if (!$dto->cortesias) {
             $dto->numero_cortesia = null;
        }

        try {
            return $this->repository->new($dto);
        } catch (Exception $e) {
            dd($e);
            // Log::error("Erro ao criar evento: " . $e->getMessage());
            throw new UsinaWeb_Exception("Não foi possível criar o evento. Erro: " . $e->getMessage());
        }
    }

    public function update(UpdateEventoDTO $dto): stdClass
    {
        // Validações de unicidade (código e URL) devem ser tratadas pelo FormRequest (ignore).

        // Ex: Verificar se data final >= data início
        if (strtotime($dto->data_final_inscricoes) < strtotime($dto->data_inicio_inscricoes)) {
            throw new UsinaWeb_Exception("A data final das inscrições não pode ser anterior à data de início.");
        }

        // Ex: Se tem cortesia, numero_cortesia deve ser > 0
        if ($dto->cortesias && (!$dto->numero_cortesia || $dto->numero_cortesia <= 0)) {
            throw new UsinaWeb_Exception("Se o evento oferece cortesias, o número de cortesias deve ser informado e maior que zero.");
        }
        // Se não tem cortesia, zerar o número para consistência
        if (!$dto->cortesias) {
             $dto->numero_cortesia = null;
        }
        
        try {
            $updated = $this->repository->update($dto);
             if (!$updated) {
                // O repositório deve retornar null se não encontrar, o que é tratado no Controller
                // Mas por segurança, podemos lançar aqui também.
                 throw new ModelNotFoundException('Evento não encontrado para atualização.');
            }
            return $updated;
        } catch (ModelNotFoundException $e) {
            throw $e; // Re-lançar para o controller tratar o 404
        } catch (Exception $e) {
            // Log::error("Erro ao atualizar evento {$dto->id}: " . $e->getMessage());
             throw new UsinaWeb_Exception("Não foi possível atualizar o evento. Erro: " . $e->getMessage());
        }
    }

    public function delete(string $id): void
    {
        try {
            $this->repository->delete($id);
        } catch (ModelNotFoundException $e) {
            // Lança a exceção original do repositório se não encontrar
             throw $e; 
        } catch (Exception $e) {
            // Log::error("Erro ao excluir evento {$id}: " . $e->getMessage());
            // Captura outras exceções genéricas (ex: foreign key constraint)
            throw new UsinaWeb_Exception("Erro ao excluir o evento: " . $e->getMessage());
        }
    }

    public function findByUrl(string $url): ?stdClass
    {
        return $this->repository->findByUrl($url);
    }
} 