<?php

namespace App\Repositories\Eloquent;

use App\DTO\CreateConfiguracaoDTO;
use App\DTO\UpdateConfiguracaoDTO;
use App\Models\Configuracao as Model;
use App\Repositories\Contracts\ConfiguracaoRepositoryInterface;
use App\Repositories\Contracts\PaginationInterface;
use App\Repositories\PaginationPresenter;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use stdClass;

class ConfiguracaoRepository implements ConfiguracaoRepositoryInterface
{
    public function __construct(
        protected Model $model
    ) {}

    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface
    {
        $result = $this->model
            ->where(function ($query) use ($filter) {
                if ($filter) {
                    $query->where('descricao_api', 'like', "%{$filter}%");
                    // Não filtrar pela chave_api
                }
            })
            ->paginate($totalPerPage, ['*'], 'page', $page);

        return new PaginationPresenter($result);
    }

    public function getAll(string $filter = null): array
    {
        return $this->model
            ->where(function ($query) use ($filter) {
                if ($filter) {
                    $query->where('descricao_api', 'like', "%{$filter}%");
                }
            })
            ->get()
            ->toArray();
    }

    public function findOne(string $id): ?stdClass
    {
        $configuracao = $this->model->find($id);
        if (!$configuracao) {
            return null;
        }
        return (object) $configuracao->toArray();
    }

    public function delete(string $id): void
    {
        if (!$this->model->find($id)) {
             throw new ModelNotFoundException('Configuração não encontrada para exclusão.');
        }
        $this->model->destroy($id);
    }

    public function new(CreateConfiguracaoDTO $dto): stdClass
    {
        $configuracao = $this->model->create((array) $dto);
        return (object) $configuracao->toArray();
    }

    public function update(UpdateConfiguracaoDTO $dto): ?stdClass
    {
        if (!$configuracao = $this->model->find($dto->id)) {
            return null;
        }

        $data = (array) $dto;
        // Não atualizar a chave se não for fornecida
        if (empty($data['chave_api'])) {
            unset($data['chave_api']);
        }
        if (empty($data['token_api'])) {
            unset($data['token_api']);
        }
        if (empty($data['webhooksecret'])) {
            unset($data['webhooksecret']);
        }
        
        $configuracao->update($data);

        return (object) $configuracao->toArray();
    }
    
    public function findByDescricao(string $descricao): ?stdClass
    {
        $configuracao = $this->model->where('descricao_api', $descricao)->first();
        if (!$configuracao) {
            return null;
        }
        return (object) $configuracao->toArray();
    }
} 