<?php

namespace App\Repositories\Eloquent;

use App\DTO\CreateEsperaDTO;
use App\DTO\UpdateEsperaDTO;
use App\Models\Espera as Model;
use App\Repositories\Contracts\EsperaRepositoryInterface;
use App\Repositories\Contracts\PaginationInterface;
use App\Repositories\PaginationPresenter; 
use App\Http\Util\UsinaWeb_Exception;
use stdClass;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class EsperaRepository implements EsperaRepositoryInterface
{
    public function __construct(
        protected Model $model
    ) {}

    public function paginate(int $page = 1, int $totalPerPage = 15, ?string $filter, ?string $evento): PaginationInterface
    {
        try {
            $query = $this->model->query();

            if ($filter) {
                $query->where(function ($q) use ($filter) {
                    $q->orWhereHas('participante', function ($subQuery) use ($filter) {
                        $subQuery->where('nome', 'like', "%{$filter}%");
                    });
                });
            }

            if ($evento) {
                $query->where('evento_codigo', $evento);
            }
            
            $query->with(['evento', 'participante']);
            $result = $query->orderBy('created_at', 'desc')->paginate($totalPerPage, ['*'], 'page', $page);
            return new PaginationPresenter($result);
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception('Erro ao paginar lista de espera: ' . $ex->getMessage());
        }
    }

    public function getAll(?string $evento): Collection
    {
        try {
            $query = $this->model->query();
            
            if ($evento) {
                $query->where(function ($q) use ($evento) {
                    $q->whereHas('evento', function ($subQuery) use ($evento) {
                        $subQuery->where('codigo', '=', "{$evento}");
                    });
                });
                
                //$query->where('evento_codigo', $evento);
            }
            // Carrega as relações para acesso no Resource/Controller
            $query->with(['evento', 'participante']);
            return $query->orderBy('created_at', 'desc')->get();
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception('Erro ao listar todos os itens da lista de espera: ' . $ex->getMessage());
        }
    }

    public function findOne(string $codigo): ?stdClass
    {
        try {
            $record = $this->model->find($codigo);
            return $record ? (object) $record->toArray() : null;
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception("Erro ao buscar item da lista de espera com código {$codigo}: " . $ex->getMessage());
        }
    }

    public function new(CreateEsperaDTO $dto): stdClass
    {
        try {
            $data = (array) $dto;
            $record = $this->model->create($data);
            return (object) $record->toArray();
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception('Erro ao criar novo item na lista de espera: ' . $ex->getMessage());
        }
    }

    public function update(UpdateEsperaDTO $dto): ?stdClass
    {
        try {
            if (!$record = $this->model->find($dto->codigo)) {
                return null; 
            }

            $dataToUpdate = [];
            if ($dto->evento_codigo !== null) $dataToUpdate['evento_codigo'] = $dto->evento_codigo;
            if ($dto->participante_codigo !== null) $dataToUpdate['participante_codigo'] = $dto->participante_codigo;

            if (empty($dataToUpdate)) { 
                return (object) $record->toArray(); 
            }
            
            $record->update($dataToUpdate);
            return (object) $record->toArray();
        } catch (ModelNotFoundException $ex) {
            throw $ex; 
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception("Erro ao atualizar item da lista de espera {$dto->codigo}: " . $ex->getMessage());
        }
    }

    public function delete(string $codigo): void
    {
        try {
            $record = $this->model->findOrFail($codigo);
            $record->delete();
        } catch (ModelNotFoundException $ex) {
            throw $ex; 
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception("Erro ao excluir item da lista de espera {$codigo}: " . $ex->getMessage());
        }
    }
    
    public function getByParticipanteEvento(string $participanteCodigo, $eventoCodigo): ?stdClass
    {
        $item = $this->model->where(function ($q) use ($participanteCodigo, $eventoCodigo) {
            $q->whereHas('participante', function ($subQuery) use ($participanteCodigo) {
                $subQuery->where('codigo', '=', "{$participanteCodigo}");
            });
            $q->whereHas('evento', function ($subQuery) use ($eventoCodigo) {
                $subQuery->where('codigo', '=', "{$eventoCodigo}");
            });
        })->first();  
        return $item ? (object) $item->toArray() : null;
    }
} 