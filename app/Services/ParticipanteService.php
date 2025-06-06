<?php

namespace App\Services;

use App\DTO\CreateParticipanteDTO;
use App\DTO\UpdateParticipanteDTO;
use App\Repositories\Contracts\ParticipanteRepositoryInterface;
use App\Repositories\Contracts\PaginationInterface;
use App\Http\Util\UsinaWeb_Exception; // Supondo que você tenha esta classe de exceção
use stdClass;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Log;

class ParticipanteService
{
    public function __construct(
        protected ParticipanteRepositoryInterface $repository
    ) {}

    public function isValidCpf(string $cpf): bool
    {
        // Extrai apenas os números
        $cpf = preg_replace('/[^0-9]/is', '', $cpf);

        // Verifica se foi informado todos os digitos corretamente
        if (strlen($cpf) != 11) {
            return false;
        }

        // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Faz o calculo para validar o CPF
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }

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

    public function new(CreateParticipanteDTO $dto): stdClass
    {
        if (!$this->isValidCpf($dto->cpf)) {
            throw new UsinaWeb_Exception("CPF inválido.", 422);
        }
        // Validação de CPF único já deve ser tratada pelo FormRequest
        // Mas podemos adicionar uma verificação extra aqui se necessário
        if ($this->repository->findByCpf($dto->cpf)) {
            throw new UsinaWeb_Exception("Já existe um participante cadastrado com este CPF.", 422); // 422 Unprocessable Entity
        }

        // Regras de negócio adicionais, se houver:
        if ($dto->participante_igreja && empty($dto->qual_igreja)) {
            throw new UsinaWeb_Exception("Se o participante frequenta uma igreja, o nome da igreja deve ser informado.", 422);
        }
        if (!$dto->participante_igreja) {
            $dto->qual_igreja = null; // Garante que qual_igreja seja nulo se não participa
        }

        if ($dto->usa_medicamento && empty($dto->qual_medicamento)) {
            throw new UsinaWeb_Exception("Se o participante usa medicamento, o nome do medicamento deve ser informado.", 422);
        }
        if (!$dto->usa_medicamento) {
            $dto->qual_medicamento = null; // Garante que qual_medicamento seja nulo se não usa
        }

        try {
            return $this->repository->new($dto);
        } catch (Exception $e) {
            Log::error("Erro ao criar participante: " . $e->getMessage());
            throw new UsinaWeb_Exception("Não foi possível criar o participante. Verifique os dados e tente novamente.");
        }
    }

    public function update(UpdateParticipanteDTO $dto): stdClass
    {
        if (!$this->isValidCpf($dto->cpf)) {
            throw new UsinaWeb_Exception("CPF inválido.", 422);
        }
        // Validação de CPF único (ignorando o próprio registro) já deve ser tratada pelo FormRequest
        $existingByCpf = $this->repository->findByCpf($dto->cpf);
        if ($existingByCpf && $existingByCpf->codigo != $dto->codigo) {
            throw new UsinaWeb_Exception("Já existe outro participante cadastrado com este CPF.", 422);
        }

        // Regras de negócio adicionais, se houver:
        if ($dto->participante_igreja && empty($dto->qual_igreja)) {
            throw new UsinaWeb_Exception("Se o participante frequenta uma igreja, o nome da igreja deve ser informado.", 422);
        }
        if (!$dto->participante_igreja) {
            $dto->qual_igreja = null;
        }

        if ($dto->usa_medicamento && empty($dto->qual_medicamento)) {
            throw new UsinaWeb_Exception("Se o participante usa medicamento, o nome do medicamento deve ser informado.", 422);
        }
        if (!$dto->usa_medicamento) {
            $dto->qual_medicamento = null;
        }

        try {
            $updated = $this->repository->update($dto);
             if (!$updated) {
                 throw new ModelNotFoundException('Participante não encontrado para atualização.');
            }
            return $updated;
        } catch (ModelNotFoundException $e) {
            throw $e; 
        } catch (Exception $e) {
            Log::error("Erro ao atualizar participante {$dto->codigo}: " . $e->getMessage());
             throw new UsinaWeb_Exception("Não foi possível atualizar o participante. Verifique os dados e tente novamente.");
        }
    }

    public function delete(string $id): void
    {
        try {
            $this->repository->delete($id);
        } catch (ModelNotFoundException $e) {
             throw $e; 
        } catch (Exception $e) {
            Log::error("Erro ao excluir participante {$id}: " . $e->getMessage());
            throw new UsinaWeb_Exception("Erro ao excluir o participante.");
        }
    }

    public function findByCpf(string $cpf): ?stdClass
    {
        if (!$this->isValidCpf($cpf)) {
            // Embora o controller vá validar, é uma boa prática ter a validação aqui também
            // ou garantir que o formato do CPF que chega aqui já está validado quanto à estrutura.
            // Por ora, a validação no controller é suficiente para o fluxo de busca.
            // Se for chamado internamente, essa validação aqui seria crucial.
            throw new UsinaWeb_Exception("CPF inválido.");
        }
        try {
            return $this->repository->findByCpf($cpf);
        } catch (Exception $e) {
            Log::error("Erro ao buscar participante por CPF {$cpf}: " . $e->getMessage());
            throw new UsinaWeb_Exception("Erro ao buscar participante por CPF.");
        }
    }
} 