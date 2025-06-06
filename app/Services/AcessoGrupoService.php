<?php

namespace App\Services;

use App\DTO\CreateAcessoGrupoDTO;
use App\DTO\UpdateAcessoGrupoDTO;
use App\Repositories\Contracts\AcessoGrupoRepositoryInterface;
use App\Repositories\Contracts\PaginationInterface;
use stdClass;

class AcessoGrupoService
{
    public function __construct(
        protected AcessoGrupoRepositoryInterface $repository
    ) {}

    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface
    {
        return $this->repository->paginate(
            page: $page,
            totalPerPage: $totalPerPage,
            filter: $filter
        );
    }

    public function getAll(string $filter = null)
    {
        return $this->repository->getAll($filter);
    }

    public function findOne(string $id): ?stdClass
    {
        return $this->repository->findOne($id);
    }

    public function new(CreateAcessoGrupoDTO $dto): stdClass
    {
        // Potentially add business logic here, e.g., checking if acesso_id and grupo_id exist
        // However, foreign key constraints and FormRequest validation should handle most of this.
        // The repository already checks for unique (acesso_id, grupo_id) combination.
        return $this->repository->new($dto);
    }

    public function update(UpdateAcessoGrupoDTO $dto): ?stdClass
    {
        // Similar to new(), business logic can be added here.
        return $this->repository->update($dto);
    }

    public function delete(string $id): void
    {
        $this->repository->delete($id);
    }

    public function findByGrupo(string $grupo_id)
    {
        return $this->repository->findByGrupo( $grupo_id);
    }

    public function getAcessosDisponiveisParaGrupo(string $grupo_id): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getAcessosDisponiveisParaGrupo($grupo_id);
    }

    /**
     * Sincroniza os acessos de um grupo.
     *
     * @param string $grupo_id
     * @param array $newAcessoIds Lista de IDs de acesso que o grupo deve ter.
     * @return void
     * @throws \Exception
     */
    public function syncAcessos(string $grupo_id, array $newAcessoIds): void
    {
        // É altamente recomendável envolver esta lógica em uma transação de banco de dados
        // para garantir a atomicidade da operação.
        // Exemplo:
        // \Illuminate\Support\Facades\DB::transaction(function () use ($grupo_id, $newAcessoIds) {

        // 1. Obter os IDs de acesso atuais para este grupo.
        //    Você precisará de um método no seu repositório como `getAcessoIdsByGrupo(string $grupo_id)`
        //    que retorne um array de acesso_id.
        //    Exemplo: $currentAcessoIds = $this->repository->getAcessoIdsByGrupo($grupo_id);
        //    Por enquanto, vamos simular buscando todos e extraindo os IDs.
        $currentAcessoGrupoEntries = $this->repository->findByGrupo($grupo_id);
        $currentAcessoIds = [];
        foreach ($currentAcessoGrupoEntries as $entry) {
            // Supondo que o objeto/array retornado pelo repositório tenha uma propriedade/chave 'acesso_id'
            if (isset($entry->acesso_id)) {
                $currentAcessoIds[] = (int)$entry->acesso_id;
            } elseif (is_array($entry) && isset($entry['acesso_id'])) {
                $currentAcessoIds[] = (int)$entry['acesso_id'];
            }
        }
        
        $newAcessoIds = array_map('intval', $newAcessoIds); // Garantir que sejam inteiros

        // 2. IDs a serem removidos (estão em $currentAcessoIds mas não em $newAcessoIds)
        $acessosToRemove = array_diff($currentAcessoIds, $newAcessoIds);

        // 3. IDs a serem adicionados (estão em $newAcessoIds mas não em $currentAcessoIds)
        $acessosToAdd = array_diff($newAcessoIds, $currentAcessoIds);

        // 4. Remover acessos
        if (!empty($acessosToRemove)) {
            // Você precisará de um método no seu repositório como
            // `deleteByGrupoAndAcessoIds(string $grupo_id, array $acessoIds)`
            // Por enquanto, vamos iterar e deletar um por um se o findOne e delete usarem o ID do AcessoGrupo
            // Se o seu método delete espera o ID da tabela 'acesso_grupo' e não o 'acesso_id',
            // esta lógica precisará ser ajustada para encontrar os IDs da tabela 'acesso_grupo' corretos.
            foreach ($currentAcessoGrupoEntries as $entry) {
                $acesso_id_do_entry = null;
                $id_acesso_grupo = null;

                if (is_object($entry) && isset($entry->acesso_id) && isset($entry->id)) {
                    $acesso_id_do_entry = (int)$entry->acesso_id;
                    $id_acesso_grupo = $entry->id;
                } elseif (is_array($entry) && isset($entry['acesso_id']) && isset($entry['id'])) {
                    $acesso_id_do_entry = (int)$entry['acesso_id'];
                    $id_acesso_grupo = $entry['id'];
                }

                if ($acesso_id_do_entry !== null && in_array($acesso_id_do_entry, $acessosToRemove)) {
                    $this->repository->delete($id_acesso_grupo); // Supondo que delete espera o ID da tabela acesso_grupo
                }
            }
        }

        // 5. Adicionar novos acessos
        foreach ($acessosToAdd as $acesso_id_to_add) {
            $dto = new CreateAcessoGrupoDTO(
                acesso_id: (string)$acesso_id_to_add, // O DTO espera string
                grupo_id: $grupo_id
            );
            $this->repository->new($dto);
        }

        // Fim da transação, se estiver usando:
        // });
    }
} 