<?php

namespace App\Http\Controllers\Api\Site;

use App\Http\Controllers\Controller;
use App\Services\InscricaoService;
use App\DTO\CreateInscricaoDTO;
use App\DTO\UpdateInscricaoDTO;
use App\Http\Requests\StoreInscricaoRequest;
use App\Http\Requests\UpdateInscricaoRequest;
use App\Http\Resources\InscricaoResource;
use App\Http\Resources\Object\InscricaoObjectResource;
use App\Adapters\ApiAdapter; // Para paginação
use App\Http\Util\UsinaWeb_Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class InscricaoController extends Controller
{
    public function __construct(
        protected InscricaoService $service
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Para carregar relações (evento, participante) para o InscricaoResource
            // Isso normalmente seria feito no service/repository ou passando um array 'with' para o service.
            // Por enquanto, o resource usará whenLoaded.
            // Se precisar sempre, o service/repository deveria usar ->with(['evento', 'participante']) 
            $inscricoesPaginadas = $this->service->paginate(
                page: $request->get('page', 1),
                totalPerPage: $request->get('per_page', 15),
                filter: $request->filter,
                evento: $request->evento
            );

            // Os itens da paginação já devem ser modelos Eloquent ou stdClass.
            // InscricaoResource espera instâncias de Model para carregar relações com whenLoaded.
            // Se o service->paginate retorna stdClass, precisariamos buscar os models ou ajustar o resource.
            // Assumindo que PaginationPresenter retorna os models originais dentro de items()

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


    public function getByParticipanteEvento(Request $request)
    {
        $inscricao = $this->service->getByParticipanteEvento($request->participante, $request->evento);
        if (!$inscricao) {
            return response()->json(["error" => "Inscrição não encontrada!"], Response::HTTP_NOT_FOUND);
        }
        $objectResource = new InscricaoObjectResource($inscricao);
        return response()->json($objectResource->toObject(), Response::HTTP_OK);
    }

    public function getCountPagasByEvento(Request $request, string $eventoCodigo)
    {
        try {
            $count = $this->service->countPagaByEvento($eventoCodigo);
            return response()->json(['count' => $count], Response::HTTP_OK);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao buscar contagem de inscrições pendentes.", "details" => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInscricaoRequest $request)
    {
        try {
            $dto = CreateInscricaoDTO::makeFromRequest($request);
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
            // Para carregar relações no show, o findOne precisaria de um with() ou o resource adaptado.
            // Assumindo que o InscricaoObjectResource não depende de relações carregadas do Eloquent.
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
        try {
            // Verifica se a inscrição existe antes de tentar atualizar
            if (!$this->service->findOne($codigo)) {
                return response()->json(["error" => "Inscrição não encontrada para atualização!"], Response::HTTP_NOT_FOUND);
            }

            $dto = UpdateInscricaoDTO::makeFromRequest($request, $codigo);
            $updatedInscricaoStdClass = $this->service->update($dto);

            if (!$updatedInscricaoStdClass) { // Pode acontecer se o update no repo retornar null por alguma razão
                return response()->json(['error' => 'Erro ao atualizar inscrição ou inscrição não encontrada!'], Response::HTTP_INTERNAL_SERVER_ERROR); 
            }

            $objectResource = new InscricaoObjectResource($updatedInscricaoStdClass);
            return response()->json($objectResource->toObject(), Response::HTTP_OK);
        } catch (ModelNotFoundException $ex) { // Captura se o service/repo lançar ModelNotFoundException no update
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
        try {
            // Verifica se a inscrição existe antes de tentar excluir
            if (!$this->service->findOne($codigo)) {
                return response()->json(["error" => "Inscrição não encontrada para exclusão!"], Response::HTTP_NOT_FOUND);
            }
            $this->service->delete($codigo);
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $ex) { // Captura se o service/repo lançar ModelNotFoundException no delete
            return response()->json(["error" => "Inscrição não encontrada para exclusão!"], Response::HTTP_NOT_FOUND);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            // Considerar erros de FK aqui, por exemplo, se inscrições não puderem ser deletadas se tiverem pagamentos associados etc.
            return response()->json(["error" => "Erro ao excluir inscrição.", "details" => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 