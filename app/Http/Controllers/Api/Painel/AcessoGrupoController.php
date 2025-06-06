<?php

namespace App\Http\Controllers\Api\Painel;

use App\Http\Controllers\Controller;
use App\Http\Resources\AcessoResource;
use App\Services\AcessoGrupoService;
use App\DTO\CreateAcessoGrupoDTO;
use App\DTO\UpdateAcessoGrupoDTO;
use App\Http\Requests\StoreAcessoGrupoRequest;
use App\Http\Requests\UpdateAcessoGrupoRequest;
use App\Http\Resources\AcessoGrupoResource;
use App\Http\Resources\Object\AcessoGrupoObjectResource;
use App\Adapters\ApiAdapter; // For pagination meta
use App\Http\Util\UsinaWeb_Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

class AcessoGrupoController extends Controller
{
    public function __construct(
        protected AcessoGrupoService $service
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $acessoGrupos = $this->service->paginate(
                page: $request->get('page', 1),
                totalPerPage: $request->get('per_page', 15),
                filter: $request->filter
            );

            return AcessoGrupoResource::collection($acessoGrupos->items())
                ->additional([
                    'meta' => ApiAdapter::pagination($acessoGrupos),
                ]);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao listar vínculos de acesso e grupo."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAll(Request $request)
    {
        try {
            $acessoGrupos = $this->service->getAll(
                filter: $request->filter
            );

            return AcessoGrupoResource::collection($acessoGrupos);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao listar todos os vínculos de acesso e grupo."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findByGrupo(string $grupo_id)
    {
        try {
            $acessoGrupos = $this->service->findByGrupo($grupo_id);
            return AcessoGrupoResource::collection($acessoGrupos);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao buscar vínculos de acesso e grupo por grupo."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAcessoGrupoRequest $request)
    {
        try {
            $acessoGrupoStdClass = $this->service->new(CreateAcessoGrupoDTO::makeFromRequest($request));
            $objectResource = new AcessoGrupoObjectResource($acessoGrupoStdClass);
            return response()->json($objectResource->toObject(), Response::HTTP_CREATED);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao criar vínculo entre acesso e grupo."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            if (!$acessoGrupoStdClass = $this->service->findOne($id)) {
                return response()->json(["error" => "Vínculo acesso-grupo não encontrado!"], Response::HTTP_NOT_FOUND);
            }
            $objectResource = new AcessoGrupoObjectResource($acessoGrupoStdClass);
            return response()->json($objectResource->toObject(), Response::HTTP_OK);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao buscar vínculo acesso-grupo."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAcessoGrupoRequest $request, string $id)
    {
        try {
            if (!$this->service->findOne($id)) {
                return response()->json(["error" => "Vínculo acesso-grupo não encontrado para atualização!"], Response::HTTP_NOT_FOUND);
            }

            $dto = UpdateAcessoGrupoDTO::makeFromRequest($request, $id);
            $updatedAcessoGrupoStdClass = $this->service->update($dto);

            //if (!$updatedAcessoGrupoStdClass) {
            //    return response()->json(['error' => 'Erro ao atualizar vínculo acesso-grupo ou não encontrado!'], Response::HTTP_INTERNAL_SERVER_ERROR); 
            //}

            $objectResource = new AcessoGrupoObjectResource($updatedAcessoGrupoStdClass);
            return response()->json($objectResource->toObject(), Response::HTTP_OK);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao atualizar vínculo acesso-grupo."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            if (!$this->service->findOne($id)) {
                return response()->json(["error" => "Vínculo acesso-grupo não encontrado para exclusão!"], Response::HTTP_NOT_FOUND);
            }
            $this->service->delete($id);
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao excluir vínculo acesso-grupo."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Lista todos os acessos disponíveis para um grupo específico (que não estão vinculados).
     */
    public function getAcessosDisponiveisParaGrupo(string $grupo_id)
    {
        try {
            $acessosDisponiveis = $this->service->getAcessosDisponiveisParaGrupo($grupo_id);
            return AcessoResource::collection($acessosDisponiveis);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $ex) {
            // Log::error("Erro em AcessoGrupoController@getAcessosDisponiveisParaGrupo (Grupo ID: {$grupo_id}): " . $ex->getMessage());
            return response()->json(["error" => "Erro ao buscar acessos disponíveis para o grupo."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Sincroniza os acessos de um grupo.
     * Remove os acessos que não estão na lista fornecida e adiciona os novos.
     */
    public function syncAcessosGrupo(Request $request, string $grupo_id)
    {
        try {
            // Validar se o grupo existe
            // Esta validação pode ser mais robusta ou feita no service
            // Exemplo: if (!$this->grupoService->findOne($grupo_id)) { return response()->json(['error' => 'Grupo não encontrado!'], Response::HTTP_NOT_FOUND); }

            $acesso_ids = $request->input('acessos'); // Espera um array de IDs de acesso. Ex: [1, 2, 3]

            if (!is_array($acesso_ids)) {
                return response()->json(["error" => "O campo 'acessos' deve ser um array de IDs."], Response::HTTP_BAD_REQUEST);
            }
            
            // Chamar o método do serviço para sincronizar os acessos
            // Este método precisará ser implementado no AcessoGrupoService
            $this->service->syncAcessos($grupo_id, $acesso_ids);

            // Após a sincronização, buscar os acessos atualizados do grupo para retornar
            $acessoGruposAtualizados = $this->service->findByGrupo($grupo_id);
            
            return AcessoGrupoResource::collection($acessoGruposAtualizados)
                ->additional(['message' => 'Acessos do grupo sincronizados com sucesso.']);

        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $ex) {
            // Adicionar log do erro para depuração
            // Log::error("Erro em AcessoGrupoController@syncAcessosGrupo (Grupo ID: {$grupo_id}): " . $ex->getMessage());
            return response()->json(["error" => "Erro ao sincronizar acessos do grupo."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}