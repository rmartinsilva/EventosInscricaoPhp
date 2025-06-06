<?php

namespace App\Http\Controllers\Api\Site;

use App\Http\Controllers\Controller;
use App\Services\EsperaService;
use App\DTO\CreateEsperaDTO;
use App\DTO\UpdateEsperaDTO;
use App\Http\Requests\StoreEsperaRequest;
use App\Http\Requests\UpdateEsperaRequest;
use App\Http\Resources\EsperaResource;
use App\Http\Resources\Object\EsperaObjectResource;
use App\Adapters\ApiAdapter; 
use App\Http\Util\UsinaWeb_Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EsperaController extends Controller
{
    public function __construct(
        protected EsperaService $service
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $esperasPaginadas = $this->service->paginate(
                page: $request->get('page', 1),
                totalPerPage: $request->get('per_page', 15),
                filter: $request->filter,
                evento: $request->evento
            );

            return EsperaResource::collection($esperasPaginadas->items())
                ->additional([
                    'meta' => ApiAdapter::pagination($esperasPaginadas),
                ]);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao listar itens da lista de espera.", "details" => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAll(Request $request)
    {
        try {
            $esperas = $this->service->getAll(
                evento: $request->evento    
            );
            return EsperaResource::collection($esperas);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao listar itens da lista de espera.", "details" => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function getByParticipanteEvento(Request $request)
    {
        $espera = $this->service->getByParticipanteEvento($request->participante, $request->evento);
        if (!$espera) {
            return response()->json(["error" => "Item não encontrado na lista de espera!"], Response::HTTP_NOT_FOUND);
        }
        $objectResource = new EsperaObjectResource($espera);
        return response()->json($objectResource->toObject(), Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEsperaRequest $request)
    {
        try {
            $dto = CreateEsperaDTO::makeFromRequest($request);
            $esperaStdClass = $this->service->new($dto);
            $objectResource = new EsperaObjectResource($esperaStdClass);
            return response()->json($objectResource->toObject(), Response::HTTP_CREATED);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao adicionar à lista de espera.", "details" => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $codigo)
    {
        try {
            if (!$esperaStdClass = $this->service->findOne($codigo)) {
                return response()->json(["error" => "Item não encontrado na lista de espera!"], Response::HTTP_NOT_FOUND);
            }
            $objectResource = new EsperaObjectResource($esperaStdClass);
            return response()->json($objectResource->toObject(), Response::HTTP_OK);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao buscar item da lista de espera.", "details" => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEsperaRequest $request, string $codigo)
    {
        try {
            if (!$this->service->findOne($codigo)) {
                return response()->json(["error" => "Item não encontrado para atualização!"], Response::HTTP_NOT_FOUND);
            }

            $dto = UpdateEsperaDTO::makeFromRequest($request, $codigo);
            $updatedEsperaStdClass = $this->service->update($dto);

            if (!$updatedEsperaStdClass) { 
                return response()->json(['error' => 'Erro ao atualizar item da lista de espera ou item não encontrado!'], Response::HTTP_INTERNAL_SERVER_ERROR); 
            }

            $objectResource = new EsperaObjectResource($updatedEsperaStdClass);
            return response()->json($objectResource->toObject(), Response::HTTP_OK);
        } catch (ModelNotFoundException $ex) { 
            return response()->json(["error" => "Item não encontrado para atualização!"], Response::HTTP_NOT_FOUND);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao atualizar item da lista de espera.", "details" => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $codigo)
    {
        try {
            if (!$this->service->findOne($codigo)) {
                return response()->json(["error" => "Item não encontrado para exclusão!"], Response::HTTP_NOT_FOUND);
            }
            $this->service->delete($codigo);
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $ex) { 
            return response()->json(["error" => "Item não encontrado para exclusão!"], Response::HTTP_NOT_FOUND);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao excluir item da lista de espera.", "details" => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 