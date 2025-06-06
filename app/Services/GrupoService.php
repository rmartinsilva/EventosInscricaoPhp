<?php

namespace App\Services;

use App\DTO\CreateGrupoDTO;
use App\DTO\UpdateGrupoDTO;
use App\Repositories\Contracts\GrupoRepositoryInterface;
use App\Repositories\Contracts\PaginationInterface;
use App\Models\Grupo; // Importar o Model para usar o relacionamento
use stdClass;
use Illuminate\Support\Facades\DB; // Para transações, se necessário

class GrupoService
{
    public function __construct(
        protected GrupoRepositoryInterface $repository
    ) {}

    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface
    {
        return $this->repository->paginate(
            page: $page,
            totalPerPage: $totalPerPage,
            filter: $filter,
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

    public function new(CreateGrupoDTO $dto): stdClass
    {
        return $this->repository->new($dto);
    }

    public function update(UpdateGrupoDTO $dto): ?stdClass
    {
        return $this->repository->update($dto);
    }

    public function delete(string $id): void
    {
        $this->repository->delete($id);
    }

    /**
     * Sincroniza os acessos de um grupo.
     *
     * @param string $id O ID do grupo.
     * @param array<int> $acessos Array com os IDs dos acessos a serem sincronizados.
     * @return bool Retorna true se bem-sucedido, false caso contrário.
     */
    public function syncAcessos(string $id, array $acessos): bool
    {
        $grupo = Grupo::find($id);
        if (!$grupo) {
            return false;
        }
        // DB::beginTransaction(); // Opcional: usar transação
        // try {
            $grupo->acessos()->sync($acessos);
            // DB::commit(); // Opcional: commit da transação
            return true;
        // } catch (\Exception $e) {
            // DB::rollBack(); // Opcional: rollback em caso de erro
            // Log::error("Erro ao sincronizar acessos para o grupo {$id}: " . $e->getMessage());
            // return false;
        // }
    }

    /**
     * Sincroniza os usuários de um grupo.
     *
     * @param string $id O ID do grupo.
     * @param array<int> $usuarios Array com os IDs dos usuários a serem sincronizados.
     * @return bool Retorna true se bem-sucedido, false caso contrário.
     */
    public function syncUsuarios(string $id, array $usuarios): bool
    {
        $grupo = Grupo::find($id);
        if (!$grupo) {
            return false;
        }
        $grupo->usuarios()->sync($usuarios);
        return true;
    }
}
