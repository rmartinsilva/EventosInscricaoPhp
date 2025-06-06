<?php

namespace App\Http\Controllers\Api\Painel;

use App\Http\Controllers\Controller;
use App\Services\EventoService;
use App\DTO\CreateEventoDTO;
use App\DTO\UpdateEventoDTO;
use App\Http\Requests\StoreEventoRequest;
use App\Http\Requests\UpdateEventoRequest;
use App\Http\Resources\EventoResource;
use App\Http\Resources\Object\EventoObjectResource; // Padrão: Usar ObjectResource
use App\Adapters\ApiAdapter; // For pagination meta
use App\Http\Util\UsinaWeb_Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

class EventoController extends Controller
{
    public function __construct(
        protected EventoService $service
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {           
            $eventos = $this->service->paginate(
                page: $request->get('page', 1),
                totalPerPage: $request->get('per_page', 15),
                filter: $request->filter
            );        
            return EventoResource::collection($eventos->items())
                ->additional([
                    'meta' => ApiAdapter::pagination($eventos),
                ]);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao listar eventos."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventoRequest $request)
    {
        try {
            $eventoStdClass = $this->service->new(CreateEventoDTO::makeFromRequest($request));
            // Padrão: Usar ObjectResource para a resposta
            $objRetorno = new EventoObjectResource($eventoStdClass);
            return response()->json($objRetorno->toObject(), Response::HTTP_CREATED);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            // Log::error('Erro ao criar evento: ' . $ex->getMessage());
            return response()->json(["error" => "Erro ao criar evento."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            if (!$eventoStdClass = $this->service->findOne($id)) {
                return response()->json(["error" => "Evento não encontrado!"], Response::HTTP_NOT_FOUND);
            }
             // Padrão: Usar ObjectResource para a resposta
            $objRetorno = new EventoObjectResource($eventoStdClass);
            return response()->json($objRetorno->toObject(), Response::HTTP_OK);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao buscar evento."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAllAtivos()
    {
        try {
            $eventos = $this->service->getAllAtivos();
            return response()->json($eventos, Response::HTTP_OK);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao buscar eventos ativos."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAll()
    {
        try {
            $eventos = $this->service->getAll();
            return response()->json($eventos, Response::HTTP_OK);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao buscar eventos."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource by URL.
     */
    public function showByUrl(string $url)
    {
        try {
            if (!$eventoStdClass = $this->service->findByUrl($url)) {
                return response()->json(["error" => "Evento não encontrado, o mesmo ainda não foi publicado ou já terminou!"], Response::HTTP_NOT_FOUND);
            }
            $objRetorno = new EventoObjectResource($eventoStdClass);
            return response()->json($objRetorno->toObject(), Response::HTTP_OK);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao buscar evento pela URL.", "details" => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventoRequest $request, string $id)
    {

        // Padrão: Verificar existência ANTES do try-catch principal
        if (!$this->service->findOne($id)) {
            return response()->json(["error" => "Evento não encontrado para atualização!"], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $dto = UpdateEventoDTO::makeFromRequest($request, $id);
            $updatedEventoStdClass = $this->service->update($dto);
            
             // Padrão: Usar ObjectResource para a resposta
            $objRetorno = new EventoObjectResource($updatedEventoStdClass);
            return response()->json($objRetorno->toObject(), Response::HTTP_OK);
        // ModelNotFoundException não deve ocorrer aqui devido à checagem prévia, mas mantemos por segurança
        } catch (ModelNotFoundException $ex) { 
            return response()->json(["error" => "Evento não encontrado para atualização!"], Response::HTTP_NOT_FOUND);
        } catch (UsinaWeb_Exception $ex) {
             return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
             // Log::error("Erro ao atualizar evento {$id}: " . $ex->getMessage());
            return response()->json(["error" => "Erro ao atualizar evento."], Response::HTTP_INTERNAL_SERVER_ERROR);
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
             return response()->json(["error" => "Evento não encontrado para exclusão!"], Response::HTTP_NOT_FOUND);
        } catch (UsinaWeb_Exception $ex) {
             return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
             // Log::error("Erro ao excluir evento {$id}: " . $ex->getMessage());
            return response()->json(["error" => "Erro ao excluir evento."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
