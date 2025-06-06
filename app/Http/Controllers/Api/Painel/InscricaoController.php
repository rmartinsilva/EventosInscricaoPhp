<?php

namespace App\Http\Controllers\Api\Painel;

use App\Http\Controllers\Controller;
use App\Services\InscricaoService;
use App\DTO\CreateInscricaoDTO;
use App\DTO\UpdateInscricaoDTO;
use App\Http\Requests\StoreInscricaoRequest;
use App\Http\Requests\UpdateInscricaoRequest;
use App\Http\Resources\InscricaoResource;
use App\Http\Resources\Object\InscricaoObjectResource;
use App\Adapters\ApiAdapter;
use App\Http\Util\UsinaWeb_Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

class InscricaoController extends Controller
{
    public function __construct(
        protected InscricaoService $service
    ) {
        // As permissões são agora tratadas diretamente nas rotas via ->middleware('can:...')
        // $this->middleware('permission:view_inscricoes')->only(['index', 'show', 'getAll']);
        // $this->middleware('permission:create_inscricoes')->only(['store']);
        // $this->middleware('permission:update_inscricoes')->only(['update']);
        // $this->middleware('permission:delete_inscricoes')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $inscricoesPaginadas = $this->service->paginate(
                page: $request->get('page', 1),
                totalPerPage: $request->get('per_page', 15),
                filter: $request->filter,
                evento: $request->evento
            );

            return InscricaoResource::collection($inscricoesPaginadas->items())
                ->additional([
                    'meta' => ApiAdapter::pagination($inscricoesPaginadas),
                ]);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao listar inscrições.", "details" => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAll(Request $request)
    {
        try {
            $inscricoes = $this->service->getAll(
                filter: $request->filter,
                evento: $request->evento    
            );
            return InscricaoResource::collection($inscricoes);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao listar inscrições.", "details" => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInscricaoRequest $request)
    {
        try {
            $data = $request->validated();
            // Definir cortesia como true por padrão para o painel
            $data['cortesia'] = true; 
            
            // Se o DTO tiver um método específico para criar a partir de um array, usar.
            // Caso contrário, instanciar diretamente ou ajustar o makeFromRequest.
            // Por simplicidade, vamos assumir que o makeFromRequest pode ser adaptado ou criaremos um DTO específico se necessário.
            // Para agora, vamos construir o DTO manualmente com o valor de cortesia modificado.
            $now = now('America/Sao_Paulo');
            $dto = new CreateInscricaoDTO(
                evento_codigo: $data['evento_codigo'] ?? $data['evento']['codigo'], // Ajustar conforme a estrutura da request
                participante_codigo: $data['participante_codigo'] ?? $data['participante']['codigo'], // Ajustar
                data: $data['data'] ?? $now->toDateTimeString(),
                forma_pagamento: $data['forma_pagamento'],
                status: $data['status'],
                cortesia: $data['cortesia'] 
            );
            
            $inscricaoStdClass = $this->service->new($dto);
            $objectResource = new InscricaoObjectResource($inscricaoStdClass);
            return response()->json($objectResource->toObject(), Response::HTTP_CREATED);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao criar inscrição.", "details" => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $codigo)
    {
        try {
            if (!$inscricaoStdClass = $this->service->findOne($codigo)) {
                return response()->json(["error" => "Inscrição não encontrada!"], Response::HTTP_NOT_FOUND);
            }
            $objectResource = new InscricaoObjectResource($inscricaoStdClass);
            return response()->json($objectResource->toObject(), Response::HTTP_OK);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao buscar inscrição.", "details" => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInscricaoRequest $request, string $codigo)
    {
        if (!$this->service->findOne($codigo)) {
            return response()->json(["error" => "Inscrição não encontrada para atualização!"], Response::HTTP_NOT_FOUND);
        }

        try {
            $dto = UpdateInscricaoDTO::makeFromRequest($request, $codigo);
            $updatedInscricaoStdClass = $this->service->update($dto);

            if (!$updatedInscricaoStdClass) {
                return response()->json(['error' => 'Erro ao atualizar inscrição ou inscrição não encontrada!'], Response::HTTP_INTERNAL_SERVER_ERROR); 
            }

            $objectResource = new InscricaoObjectResource($updatedInscricaoStdClass);
            return response()->json($objectResource->toObject(), Response::HTTP_OK);
        } catch (ModelNotFoundException $ex) { 
            return response()->json(["error" => "Inscrição não encontrada para atualização!"], Response::HTTP_NOT_FOUND);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao atualizar inscrição.", "details" => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $codigo)
    {
        if (!$this->service->findOne($codigo)) {
            return response()->json(["error" => "Inscrição não encontrada para exclusão!"], Response::HTTP_NOT_FOUND);
        }
        try {
            $this->service->delete($codigo);
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $ex) { 
            return response()->json(["error" => "Inscrição não encontrada para exclusão!"], Response::HTTP_NOT_FOUND);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao excluir inscrição.", "details" => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getCountCortesiaByEvento(Request $request, string $eventoCodigo)
    {
        try {
            $count = $this->service->countCortesiaByEvento($eventoCodigo);
            return response()->json(['count' => $count], Response::HTTP_OK);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao buscar contagem de inscrições pendentes.", "details" => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAllByEvento(Request $request, string $eventoCodigo)
    {
        try {
            $filter = $request->filter ? filter_var($request->filter, FILTER_VALIDATE_BOOLEAN) : null;
            $inscricoes = $this->service->getAllByEvento($eventoCodigo, $filter);
            return InscricaoResource::collection($inscricoes);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao listar inscrições do evento.", "details" => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 