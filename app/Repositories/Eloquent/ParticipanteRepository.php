<?php

namespace App\Repositories\Eloquent;

use App\DTO\CreateParticipanteDTO;
use App\DTO\UpdateParticipanteDTO;
use App\Models\Participante as Model;
use App\Repositories\Contracts\ParticipanteRepositoryInterface;
use App\Repositories\Contracts\PaginationInterface;
use App\Repositories\PaginationPresenter;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use stdClass;

class ParticipanteRepository implements ParticipanteRepositoryInterface
{
    public function __construct(
        protected Model $model
    ) {}

    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface
    {
        $result = $this->model
            ->where(function ($query) use ($filter) {
                if ($filter) {
                    $query->where('nome', 'like', "%{$filter}%")
                          ->orWhere('cpf', 'like', "%{$filter}%")
                          ->orWhere('email', 'like', "%{$filter}%");
                }
            })
            ->orderBy('nome', 'asc')
            ->paginate($totalPerPage, ['*'], 'page', $page);

        return new PaginationPresenter($result);
    }

    public function getAll(string $filter = null): array
    {
        return $this->model
            ->where(function ($query) use ($filter) {
                if ($filter) {
                    $query->where('nome', 'like', "%{$filter}%")
                          ->orWhere('cpf', 'like', "%{$filter}%")
                          ->orWhere('email', 'like', "%{$filter}%");
                }
            })
            ->orderBy('nome', 'asc')
            ->get()
            ->toArray();
    }

    public function findOne(string $id): ?stdClass
    {
        $participante = $this->model->find($id);
        if (!$participante) {
            return null;
        }
        return (object) $participante->toArray();
    }

    public function delete(string $id): void
    {
        if (!$this->model->find($id)) {
             throw new ModelNotFoundException('Participante não encontrado para exclusão.');
        }
        $this->model->destroy($id);
    }

    public function new(CreateParticipanteDTO $dto): stdClass
    {
        $participante = $this->model->create((array) $dto);
        return (object) $participante->toArray();
    }

    public function update(UpdateParticipanteDTO $dto): ?stdClass
    {
        if (!$participante = $this->model->find($dto->codigo)) {
            return null;
        }
        $data = (array) $dto;
        unset($data['codigo']); // Não atualiza o código
        
        $participante->update($data);
        return (object) $participante->toArray();
    }

    public function findByCpf(string $cpf): ?stdClass
    {
        $participante = $this->model->where('cpf', $cpf)->first();
        return $participante ? (object) $participante->toArray() : null;
    }
} 