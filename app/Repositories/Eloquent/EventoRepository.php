<?php

namespace App\Repositories\Eloquent;

use App\DTO\CreateEventoDTO;
use App\DTO\UpdateEventoDTO;
use App\Models\Evento as Model;
use App\Repositories\Contracts\EventoRepositoryInterface;
use App\Repositories\Contracts\PaginationInterface;
use App\Repositories\PaginationPresenter;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Log;
use stdClass;
use Carbon\Carbon;

class EventoRepository implements EventoRepositoryInterface
{
    public function __construct(
        protected Model $model
    ) {}

    public function paginate(int $page = 1, int $totalPerPage = 15, ?string $filter = null): PaginationInterface
    {
        $result = $this->model
            ->where(function ($query) use ($filter) {
                if ($filter) {
                    $query->where('descricao', 'like', "%{$filter}%")
                          ->orWhere('codigo', 'like', "%{$filter}%");
                }
            })
            ->orderBy('data', 'desc') // Ordenar por data mais recente por padrão
            ->paginate($totalPerPage, ['*'], 'page', $page);

        return new PaginationPresenter($result);
    }

    public function getAll(?string $filter = null): array
    {
        return $this->model
            ->where(function ($query) use ($filter) {
                if ($filter) {
                    $query->where('descricao', 'like', "%{$filter}%")
                          ->orWhere('codigo', 'like', "%{$filter}%");
                }
            })
            ->orderBy('data', 'desc')
            ->get()
            ->toArray();
    }

    public function findOne(string $id): ?stdClass
    {
        $evento = $this->model->find($id);
        if (!$evento) {
            return null;
        }
        return (object) $evento->toArray();
    }

    public function delete(string $id): void
    {
        if (!$this->model->find($id)) {
             throw new ModelNotFoundException('Evento não encontrado para exclusão.');
        }
        $this->model->destroy($id);
    }

    public function new(CreateEventoDTO $dto): stdClass
    {
        $evento = $this->model->create((array) $dto);
        return (object) $evento->toArray();
    }

    public function update(UpdateEventoDTO $dto): ?stdClass
    {
        if (!$evento = $this->model->find($dto->codigo)) {
            return null;
        }
        $data = (array) $dto;
        unset($data['codigo']);
        
        $evento->update($data);
        return (object) $evento->toArray();
    }

    public function findByCodigo(string $codigo): ?stdClass
    {
        $evento = $this->model->where('codigo', $codigo)->first();
        return $evento ? (object) $evento->toArray() : null;
    }

    public function getAllAtivos()
    {
        $now = now('America/Sao_Paulo'); // Usar o helper now() do Laravel
        return $this->model
        ->where('data_inicio_inscricoes', '<', $now)
        ->where('data_final_inscricoes', '>=', $now)
        ->orderBy('data', 'asc')
        ->get();
    }

    public function findByUrl(string $url): ?stdClass
    {
        $now = now('America/Sao_Paulo'); // Usar o helper now() do Laravel
        //Log::info('now: ' . $now);
        $evento = $this->model
            ->where('url', $url)
            ->where('data_inicio_inscricoes', '<', $now)
            ->where('data_final_inscricoes', '>=', $now)
            ->first();
        
        return $evento ? (object) $evento->toArray() : null;
    }
} 