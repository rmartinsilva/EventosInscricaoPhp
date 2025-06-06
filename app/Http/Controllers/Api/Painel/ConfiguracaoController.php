<?php

namespace App\Http\Controllers\Api\Painel;

use App\Http\Controllers\Controller;
use App\Http\Resources\Object\ConfiguracaoObjectResource;
use App\Models\Configuracao;
use App\Services\ConfiguracaoService;
use App\DTO\CreateConfiguracaoDTO;
use App\DTO\UpdateConfiguracaoDTO;
use App\Http\Requests\StoreConfiguracaoRequest;
use App\Http\Requests\UpdateConfiguracaoRequest;
use App\Http\Resources\ConfiguracaoResource;
use App\Adapters\ApiAdapter; // For pagination meta
use App\Http\Util\UsinaWeb_Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

class ConfiguracaoController extends Controller
{
    public function __construct(
        protected ConfiguracaoService $service
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $configuracoes = $this->service->paginate(
                page: $request->get('page', 1),
                totalPerPage: $request->get('per_page', 15),
                filter: $request->filter
            );

            return ConfiguracaoResource::collection($configuracoes->items())
                ->additional([
                    'meta' => ApiAdapter::pagination($configuracoes),
                ]);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao listar configurações."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConfiguracaoRequest $request)
    {
        try {
            $configuracaoStdClass = $this->service->new(CreateConfiguracaoDTO::makeFromRequest($request));
            // Usar o Resource para formatar a resposta
            $objRetorno = new ConfiguracaoObjectResource($configuracaoStdClass);
            return response()->json($objRetorno->toObject(), Response::HTTP_CREATED);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST); 
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao criar configuração."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) // Alterado de Configuracao $configuracao para usar o service
    {
        try {
            if (!$configuracaoStdClass = $this->service->findOne($id)) {
                return response()->json(["error" => "Configuração não encontrada!"], Response::HTTP_NOT_FOUND);
            }            
            $objRetorno = new ConfiguracaoObjectResource($configuracaoStdClass);
            return response()->json($objRetorno->toObject(), Response::HTTP_OK);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao buscar configuração."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConfiguracaoRequest $request, string $id)
    {
         // 1. Verifica se a configuração existe
         if (!$configuracao = $this->service->findOne($id)) {
            return response()->json([
                "error" => "Configuração não encontrada!"
            ], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $dto = UpdateConfiguracaoDTO::makeFromRequest($request, $id);
            $updatedConfiguracaoStdClass = $this->service->update($dto);
            
            $objRetorno = new ConfiguracaoObjectResource($updatedConfiguracaoStdClass);
            return response()->json($objRetorno->toObject(), Response::HTTP_OK);
        } catch (ModelNotFoundException $ex) {
            return response()->json(["error" => "Configuração não encontrada para atualização!"], Response::HTTP_NOT_FOUND);
        } catch (UsinaWeb_Exception $ex) {
             return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao atualizar configuração."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->service->delete($id);
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $ex) {
             return response()->json(["error" => "Configuração não encontrada para exclusão!"], Response::HTTP_NOT_FOUND);
        } catch (UsinaWeb_Exception $ex) {
             return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao excluir configuração."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
