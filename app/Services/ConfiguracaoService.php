<?php

namespace App\Services;

use App\DTO\CreateConfiguracaoDTO;
use App\DTO\UpdateConfiguracaoDTO;
use App\Repositories\Contracts\ConfiguracaoRepositoryInterface;
use App\Repositories\Contracts\PaginationInterface;
use App\Http\Util\UsinaWeb_Exception;
use stdClass;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class ConfiguracaoService
{
    public function __construct(
        protected ConfiguracaoRepositoryInterface $repository
    ) {}

    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface
    {
        return $this->repository->paginate(
            page: $page,
            totalPerPage: $totalPerPage,
            filter: $filter
        );
    }

    public function getAll(string $filter = null): array
    {
        return $this->repository->getAll($filter);
    }

    public function findOne(string $id): ?stdClass
    {
        return $this->repository->findOne($id);
    }

    public function new(CreateConfiguracaoDTO $dto): stdClass
    {
        // Verificar se descrição já existe
        if ($this->repository->findByDescricao($dto->descricao_api)) {
             throw new UsinaWeb_Exception("Já existe uma configuração com a descrição '{$dto->descricao_api}'.");
        }
        return $this->repository->new($dto);
    }

    public function update(UpdateConfiguracaoDTO $dto): stdClass
    {
         // Verificar se a nova descrição já existe em outro registro
        $existing = $this->repository->findByDescricao($dto->descricao_api);
        if ($existing && $existing->id != $dto->id) {
            throw new UsinaWeb_Exception("A descrição '{$dto->descricao_api}' já está em uso por outra configuração.");
        }
        
        $updated = $this->repository->update($dto);
        if (!$updated) {
            // A exceção ModelNotFoundException deve ser lançada pelo repositório se não encontrado
             throw new ModelNotFoundException('Configuração não encontrada para atualização.');
        }
        return $updated;
    }

    public function delete(string $id): void
    {
        try {
            $this->repository->delete($id);
        } catch (ModelNotFoundException $e) {
            // Lança a exceção original do repositório se não encontrar
             throw $e; 
        } catch (Exception $e) {
            // Captura outras exceções genéricas
            throw new UsinaWeb_Exception("Erro ao excluir a configuração: " . $e->getMessage());
        }
    }
} 