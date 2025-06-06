<?php

namespace App\Repositories\Eloquent;

use App\DTO\CreateInscricaoDTO;
use App\DTO\UpdateInscricaoDTO;
use App\Models\Inscricao as Model;
use App\Repositories\Contracts\InscricaoRepositoryInterface;
use App\Repositories\Contracts\PaginationInterface;
use App\Repositories\PaginationPresenter; // Presumindo que existe e é usado
use App\Http\Util\UsinaWeb_Exception; // Presumindo que existe para tratamento de erro
use stdClass;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class InscricaoRepository implements InscricaoRepositoryInterface
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

                    /*$q->where('forma_pagamento', 'like', "%{$filter}%")
                      ->orWhere('status', 'like', "%{$filter}%");*/

                    // Adiciona busca pelo nome do participante
                    $q->orWhereHas('participante', function ($subQuery) use ($filter) {
                        $subQuery->where('nome', 'like', "%{$filter}%");
                    });
                });
            }

            $query->where('cortesia', '=', true);

            if ($evento) {
                $query->where('evento_codigo', $evento);
            }
            
            // Carrega as relações para acesso no Resource/Controller
            $query->with(['evento', 'participante']);

            $result = $query->orderBy('data', 'desc')->paginate($totalPerPage, ['*'], 'page', $page);
            return new PaginationPresenter($result);
        } catch (Exception $ex) {
            // Em um projeto real, logar o erro $ex->getMessage()
            throw new UsinaWeb_Exception('Erro ao paginar inscrições: ' . $ex->getMessage());
        }
    }

    public function getByParticipanteEvento(string $participanteCodigo, $eventoCodigo): ?stdClass
    {
        $inscricao = $this->model->where(function ($q) use ($participanteCodigo, $eventoCodigo) {
            $q->whereHas('participante', function ($subQuery) use ($participanteCodigo) {
                $subQuery->where('codigo', '=', "{$participanteCodigo}");
            });
            $q->whereHas('evento', function ($subQuery) use ($eventoCodigo) {
                $subQuery->where('codigo', '=', "{$eventoCodigo}");
            });
        })->first();  
        return $inscricao ? (object) $inscricao->toArray() : null;
    }

    public function getAll(?string $filter, ?string $evento)
    {
        try {
            $query = $this->model->query();

            if ($filter) {
                $query->where(function ($q) use ($filter) {
                    /*
                    $q->where('forma_pagamento', 'like', "%{$filter}%")
                      ->orWhere('status', 'like', "%{$filter}%")
                      ->orWhere('evento_codigo', $filter)
                      ->orWhere('participante_codigo', $filter);
                      */
                      $q->orWhereHas('participante', function ($subQuery) use ($filter) {
                        $subQuery->where('nome', 'like', "%{$filter}%");
                    });
                });
            }
            
            if ($evento) {
                $query->where('evento_codigo', $evento);
            }
            
            // Carrega as relações para acesso no Resource/Controller
            $query->with(['evento', 'participante']);
            return $query->orderBy('data', 'desc')->get(); // Retorna array de arrays
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception('Erro ao listar todas as inscrições: ' . $ex->getMessage());
        }
    }

    public function getAllByEvento(string $eventoCodigo, ?bool $filter)
    {
        try {   
            $query = $this->model->query();
            $query->where('evento_codigo', $eventoCodigo);
            if ($filter !== null) {
                $query->where('cortesia', '=', $filter);
            }
            $query->with(['evento', 'participante']);
            return $query->orderBy('data', 'desc')->get(); // Retorna array de arrays
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception('Erro ao listar todas as inscrições do evento: ' . $ex->getMessage());
        }
    }

    public function findOne(string $codigo): ?stdClass
    {
        try {
            $record = $this->model->find($codigo);
            // if ($record) $record->load(['evento', 'participante']); // Opcional: carregar relações
            return $record ? (object) $record->toArray() : null;
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception("Erro ao buscar inscrição com código {$codigo}: " . $ex->getMessage());
        }
    }

    public function new(CreateInscricaoDTO $dto): stdClass
    {
        try {
            $data = (array) $dto;
            // Garantir que cortesia seja tratada corretamente se for null no DTO
            if (!isset($data['cortesia'])) {
                $data['cortesia'] = null; // ou false, dependendo da regra de negócio para o default
            }
            $record = $this->model->create($data);
            return (object) $record->toArray();
        } catch (Exception $ex) {
            // Tratar QueryException para chaves estrangeiras inválidas, etc.
            throw new UsinaWeb_Exception('Erro ao criar nova inscrição: ' . $ex->getMessage());
        }
    }

    public function update(UpdateInscricaoDTO $dto): ?stdClass
    {
        try {
            if (!$record = $this->model->find($dto->codigo)) {
                return null; // Ou lançar ModelNotFoundException
            }

            $dataToUpdate = [];
            // Montar array apenas com os campos que foram enviados e não são nulos no DTO
            // (exceto cortesia que pode ser explicitamente null para limpar ou false)
            if ($dto->evento_codigo !== null) $dataToUpdate['evento_codigo'] = $dto->evento_codigo;
            if ($dto->participante_codigo !== null) $dataToUpdate['participante_codigo'] = $dto->participante_codigo;
            if ($dto->data !== null) $dataToUpdate['data'] = $dto->data;
            if ($dto->forma_pagamento !== null) $dataToUpdate['forma_pagamento'] = $dto->forma_pagamento;
            if ($dto->status !== null) $dataToUpdate['status'] = $dto->status;
            
            // Tratamento especial para cortesia, pois pode ser definido como true, false ou null (para limpar)
            // Se a propriedade existe no DTO (foi enviada na request), atualiza.
            // O DTO já garante que se não foi enviado, é null, mas aqui estamos sendo explícitos.
            if (property_exists($dto, 'cortesia')) {
                 $dataToUpdate['cortesia'] = $dto->cortesia; 
            }

            if (empty($dataToUpdate)) { // Nada para atualizar
                return (object) $record->toArray(); // Retorna o registro existente
            }
            
            $record->update($dataToUpdate);
            return (object) $record->toArray();
        } catch (ModelNotFoundException $ex) {
            throw $ex; // Re-lança para ser tratado pelo Service/Controller
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception("Erro ao atualizar inscrição {$dto->codigo}: " . $ex->getMessage());
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
            // Tratar exceções de restrição de chave estrangeira se houver deleções restritas
            throw new UsinaWeb_Exception("Erro ao excluir inscrição {$codigo}: " . $ex->getMessage());
        }
    }

    /**
     * Busca inscrições existentes para um determinado evento e participante.
     *
     * @param string $eventoCodigo
     * @param string $participanteCodigo
     * @return \Illuminate\Support\Collection Coleção de stdClass representando as inscrições.
     */
    public function findExisting(string $eventoCodigo, string $participanteCodigo): \Illuminate\Support\Collection
    {
        try {
            return $this->model
                ->where('evento_codigo', $eventoCodigo)
                ->where('participante_codigo', $participanteCodigo)
                ->get()
                ->map(function ($inscricao) {
                    return (object) $inscricao->toArray();
                });
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception("Erro ao buscar inscrições existentes para evento {$eventoCodigo} e participante {$participanteCodigo}: " . $ex->getMessage());
        }
    }

    /**
     * Conta o número de inscrições paga (status 'P') para um evento específico.
     *
     * @param string $eventoCodigo
     * @return int
     */
    public function countPagaByEvento(string $eventoCodigo): int
    {
        try {
            return $this->model
                ->where('evento_codigo', $eventoCodigo)
                ->where('status', 'P')
                ->where('cortesia', '=', false)
                ->count();
        } catch (Exception $ex) {
            // Em um projeto real, logar o erro $ex->getMessage()
            throw new UsinaWeb_Exception("Erro ao contar inscrições pagas para o evento {$eventoCodigo}: " . $ex->getMessage());
        }
    }

       /**
     * Conta o número de inscrições paga (status 'P') para um evento específico.
     *
     * @param string $eventoCodigo
     * @return int
     */
    public function countCortesiaByEvento(string $eventoCodigo): int
    {
        try {
            return $this->model
                ->where('evento_codigo', $eventoCodigo)
                ->where('status', 'C')
                ->where('cortesia', '=', true)
                ->count();
        } catch (Exception $ex) {
            // Em um projeto real, logar o erro $ex->getMessage()
            throw new UsinaWeb_Exception("Erro ao contar inscrições cortesias para o evento {$eventoCodigo}: " . $ex->getMessage());
        }
    }

    
} 