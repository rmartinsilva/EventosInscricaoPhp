<?php

namespace App\Http\Controllers\Api\Painel;

use App\Http\Controllers\Controller;
use App\Http\Util\UsinaWeb_Exception;
use Exception;
use Illuminate\Http\Request;
use App\Services\GrupoService;
use App\DTO\CreateGrupoDTO;
use App\DTO\UpdateGrupoDTO;
use App\Http\Resources\GrupoResource;
use App\Http\Resources\Object\GrupoObjectResource;
use App\Adapters\ApiAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Http\Requests\StoreGrupoRequest;
use App\Http\Requests\UpdateGrupoRequest;

class GrupoController extends Controller
{
    public function __construct(
        protected GrupoService $service
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $grupos = $this->service->paginate(
            page: $request->get('page', 1),
            totalPerPage: $request->get('per_page', 15),
            filter: $request->filter,
        );

        return GrupoResource::collection($grupos->items())
            ->additional([
                'meta' => ApiAdapter::pagination($grupos),
            ]);
    }

    public function getAll(Request $request)
    {
        $grupos = $this->service->getAll(
            filter: $request->filter,
        );
        return GrupoResource::collection($grupos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGrupoRequest $request)
    {
       
        try {
            $grupoStdClass = $this->service->new(CreateGrupoDTO::makeFromRequest($request));

            $objRetorno = new GrupoObjectResource($grupoStdClass);
            return response()->json($objRetorno->toObject(), Response::HTTP_CREATED);
        } catch (UsinaWeb_Exception $ex) {
          
            return response()->json([
                "error" => $ex->getMensagem()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $ex) {
            return response()->json([
                "error" => "Erro ao criar grupo! " . $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (!$grupoStdClass = $this->service->findOne($id)) {
            return response()->json(["error" => "Grupo não encontrado!"], Response::HTTP_NOT_FOUND);
        }

        $objRetorno = new GrupoObjectResource($grupoStdClass);
        return $objRetorno->toObject();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGrupoRequest $request, string $id)
    {
        // 1. Verifica se o grupo existe
        if (!$grupo = $this->service->findOne($id)) {
            return response()->json([
                "error" => "Grupo não encontrado!"
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            // 2. Cria o DTO e tenta atualizar
            $dto = UpdateGrupoDTO::makeFromRequest($request, $id);
            $grupoAtualizado = $this->service->update($dto);

            // 3. Verifica se a atualização falhou
            /*if (!$grupoAtualizado) {
                return response()->json([
                    "error" => "Erro ao atualizar grupo!"
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }*/

            // 4. Retorna o grupo atualizado
            $objRetorno = new GrupoObjectResource($grupoAtualizado);
            return response()->json($objRetorno->toObject(), Response::HTTP_OK);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json([
                "error" => $ex->getMensagem()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $ex) {
            return response()->json([
                "error" => "Erro ao atualizar grupo! " . $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!$this->service->findOne($id)) {
            return response()->json(["error" => "Grupo não encontrado!"], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->service->delete($id);
            return response()->json([], Response::HTTP_NO_CONTENT);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json([
                "error" => $ex->getMensagem()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $ex) {
            return response()->json([
                "error" => "Erro ao deletar grupo! " . $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Sincroniza os acessos de um grupo.
     */
    public function syncAcessos(Request $request, string $grupoId)
    {
        $this->service->syncAcessos($grupoId, $request->input('acessos_ids', []));
        return response()->json(['message' => 'Acessos sincronizados com sucesso.']);
    }

    /**
     * Sincroniza os usuários de um grupo.
     */
    public function syncUsuarios(Request $request, string $grupoId)
    {
        $this->service->syncUsuarios($grupoId, $request->input('usuarios_ids', []));
        return response()->json(['message' => 'Usuários sincronizados com sucesso.']);
    }
}
