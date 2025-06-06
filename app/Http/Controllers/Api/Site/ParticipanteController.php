<?php

namespace App\Http\Controllers\Api\Site;

use App\Http\Controllers\Controller; // Controller base
use App\Services\ParticipanteService;
use App\DTO\CreateParticipanteDTO;
use App\DTO\UpdateParticipanteDTO;
use App\Http\Requests\StoreParticipanteRequest;
use App\Http\Requests\UpdateParticipanteRequest;
use App\Http\Resources\ParticipanteResource; // Será criado
use App\Http\Resources\Object\ParticipanteObjectResource; // Será criado
use App\Adapters\ApiAdapter; // Para paginação
use App\Http\Util\UsinaWeb_Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

class ParticipanteController extends Controller
{
    public function __construct(
        protected ParticipanteService $service
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $participantes = $this->service->paginate(
                page: $request->get('page', 1),
                totalPerPage: $request->get('per_page', 15),
                filter: $request->filter
            );
            return ParticipanteResource::collection($participantes->items())
                ->additional([
                    'meta' => ApiAdapter::pagination($participantes),
                ]);
        }   catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        }  catch (Exception $ex) {
            return response()->json(["error" => "Erro ao listar participantes.", "details" => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreParticipanteRequest $request)
    {
        try {
            $participanteStdClass = $this->service->new(CreateParticipanteDTO::makeFromRequest($request));
            $objRetorno = new ParticipanteObjectResource($participanteStdClass);
            return response()->json($objRetorno->toObject(), Response::HTTP_CREATED);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao criar participante.", "details" => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            if (!$participanteStdClass = $this->service->findOne($id)) {
                return response()->json(["error" => "Participante não encontrado!"], Response::HTTP_NOT_FOUND);
            }
            $objRetorno = new ParticipanteObjectResource($participanteStdClass);
            return response()->json($objRetorno->toObject(), Response::HTTP_OK);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao buscar participante.", "details" => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateParticipanteRequest $request, string $id)
    {
        if (!$this->service->findOne($id)) {
            return response()->json(["error" => "Participante não encontrado para atualização!"], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $dto = UpdateParticipanteDTO::makeFromRequest($request, (int)$id);
            $updatedParticipanteStdClass = $this->service->update($dto);
            
            $objRetorno = new ParticipanteObjectResource($updatedParticipanteStdClass);
            return response()->json($objRetorno->toObject(), Response::HTTP_OK);
        } catch (ModelNotFoundException $ex) { 
            return response()->json(["error" => "Participante não encontrado para atualização!"], Response::HTTP_NOT_FOUND);
        } catch (UsinaWeb_Exception $ex) {
             return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao atualizar participante.", "details" => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
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
             return response()->json(["error" => "Participante não encontrado para exclusão!"], Response::HTTP_NOT_FOUND);
        } catch (UsinaWeb_Exception $ex) {
             return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao excluir participante.", "details" => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Search participant by CPF.
     */
    public function searchByCpf(Request $request)
    {
        try {
            $cpf = $request->get('cpf');
            
            if (!$cpf) {
                return response()->json(["error" => "CPF não fornecido!"], Response::HTTP_BAD_REQUEST);
            }

            // Remove any non-numeric characters from the CPF for validation
            $cleanedCpf = preg_replace('/[^0-9]/', '', $cpf);

            if (!$this->service->isValidCpf($cleanedCpf)) {
                return response()->json(["error" => "CPF inválido!"], Response::HTTP_BAD_REQUEST);
            }
            
            // Format CPF to match stored format (XXX.XXX.XXX-XX) only after validation
            // The isValidCpf method already handles cleaned CPF, so we use $cleanedCpf for formatting as well.
            $formattedCpf = substr($cleanedCpf, 0, 3) . '.' . 
                           substr($cleanedCpf, 3, 3) . '.' . 
                           substr($cleanedCpf, 6, 3) . '-' . 
                           substr($cleanedCpf, 9, 2);

            $participanteStdClass = $this->service->findByCpf($formattedCpf);
            
            if (!$participanteStdClass) {
                return response()->json(["error" => "Participante não encontrado!"], Response::HTTP_NOT_FOUND);
            }

            $objRetorno = new ParticipanteObjectResource($participanteStdClass);
            return response()->json($objRetorno->toObject(), Response::HTTP_OK);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao buscar participante por CPF.", "details" => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 