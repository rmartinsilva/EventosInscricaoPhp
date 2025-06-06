<?php

namespace App\Http\Controllers\Api\Painel;

use App\Http\Controllers\Controller;
use App\Http\Util\UsinaWeb_Exception;
use Exception;
use Illuminate\Http\Request;
use App\Services\GrupoUsuarioService;
use App\DTO\CreateGrupoUsuarioDTO;
use App\DTO\UpdateGrupoUsuarioDTO;
use App\Http\Resources\GrupoUsuarioResource;
use App\Http\Resources\Object\GrupoUsuarioObjectResource;
use App\Adapters\ApiAdapter;
use Illuminate\Http\Response;
use App\Http\Requests\StoreGrupoUsuarioRequest;
use App\Http\Requests\UpdateGrupoUsuarioRequest;
use App\Http\Resources\GrupoResource;

class GrupoUsuarioController extends Controller
{
    public function __construct(
        protected GrupoUsuarioService $service
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $grupoUsuarios = $this->service->paginate(
            page: $request->get('page', 1),
            totalPerPage: $request->get('per_page', 15),
            filter: $request->filter,
        );

        return GrupoUsuarioResource::collection($grupoUsuarios->items())
                                ->additional([
                                    'meta' => ApiAdapter::pagination($grupoUsuarios),
                                ]);
    }

    public function getAll(Request $request)
    {
        $grupoUsuarios = $this->service->getAll(
            filter: $request->filter,
        );
        return GrupoUsuarioResource::collection($grupoUsuarios);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGrupoUsuarioRequest $request)
    {
        
        try {
            $grupoUsuario = $this->service->new(CreateGrupoUsuarioDTO::makeFromRequest($request));
            $objRetorno = new GrupoUsuarioObjectResource($grupoUsuario);
            return response()->json($objRetorno->toObject(), Response::HTTP_CREATED);
        } 
        catch (UsinaWeb_Exception $ex) {
            return response()->json([
                "error" => $ex->getMensagem()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        catch (Exception $ex) {
            return response()->json([
                "error" => "Erro ao criar relação Grupo-Usuário1! " . $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        //$grupoUsuario = $this->service->new(CreateGrupoUsuarioDTO::makeFromRequest($request));
        //$objRetorno = new GrupoUsuarioObjectResource($grupoUsuario);

        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (!$grupoUsuario = $this->service->findOne($id)) {
            return response()->json([
                "error" => "Relação Grupo-Usuário não encontrada!"
            ], Response::HTTP_NOT_FOUND);
        }

        $objRetorno = new GrupoUsuarioObjectResource($grupoUsuario);
        return $objRetorno->toObject();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGrupoUsuarioRequest $request, string $id)
    {
        // 1. Verifica se a relação existe
        if (!$grupoUsuario = $this->service->findOne($id)) {
            return response()->json([
                "error" => "Relação Grupo-Usuário não encontrada!"
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            // 2. Cria o DTO e tenta atualizar
            $dto = UpdateGrupoUsuarioDTO::makeFromRequest($request, $id);
            $grupoUsuarioAtualizado = $this->service->update($dto);

        // 3. Verifica se a atualização falhou
        /*if (!$grupoUsuarioAtualizado) {
            return response()->json([
                "error" => "Erro ao atualizar relação Grupo-Usuário!"
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }*/

        // 4. Retorna a relação atualizada
        $objRetorno = new GrupoUsuarioObjectResource($grupoUsuarioAtualizado);
        return response()->json($objRetorno->toObject(), Response::HTTP_OK);
        }
        catch (UsinaWeb_Exception $ex) {
            return response()->json([
                "error" => $ex->getMensagem()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        catch (Exception $ex) {
            return response()->json([
                "error" => "Erro ao atualizar relação Grupo-Usuário! " . $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!$this->service->findOne($id)) {
            return response()->json([
                "error" => "Relação Grupo-Usuário não encontrada!"
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->service->delete($id);
            return response()->json([], Response::HTTP_NO_CONTENT);
        }
        catch (UsinaWeb_Exception $ex) {
            return response()->json([
                "error" => $ex->getMensagem()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        catch (Exception $ex) {
            return response()->json([
                "error" => "Erro ao deletar relação Grupo-Usuário! " . $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }    
        
    }

    /**
     * Lista todos os grupos disponíveis para um usuário específico.
     * 
     * @param Request $request
     * @param string $usuario_id O ID do usuário para verificar grupos disponíveis
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGruposDisponiveis(Request $request, string $usuario_id)
    {
        try {
            $grupos = $this->service->getGruposDisponiveis(
                usuario_id: $usuario_id
            );
            return GrupoResource::collection($grupos);
        }
        catch (UsinaWeb_Exception $ex) {
            return response()->json([
                "error" => $ex->getMensagem()
            ], $ex->getStatusCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        catch (Exception $ex) {
            return response()->json([
                "error" => "Erro ao buscar grupos disponíveis para o usuário!"
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 