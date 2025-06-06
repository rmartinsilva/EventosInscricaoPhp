<?php

namespace App\Http\Controllers\Api\Painel;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAcessoRequest;
use App\Http\Requests\UpdateAcessoRequest;
use App\Http\Util\UsinaWeb_Exception;
use Exception;
use Illuminate\Http\Request;
use App\Services\AcessoService;
use App\DTO\CreateAcessoDTO;
use App\DTO\UpdateAcessoDTO;
use App\Http\Resources\AcessoResource;
use App\Http\Resources\Object\AcessoObjectResource;
use App\Adapters\ApiAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;


class AcessoController extends Controller
{
    public function __construct(
        protected AcessoService $service
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $acessos = $this->service->paginate(
            page: $request->get('page', 1),
            totalPerPage: $request->get('per_page', 15),
            filter: $request->filter,
        );

        return AcessoResource::collection($acessos->items())
            ->additional([
                'meta' => ApiAdapter::pagination($acessos),
            ]);
    }

    public function getAll(Request $request)
    {
        try {
            $acessos = $this->service->getAll();
            return AcessoResource::collection($acessos);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json(["error" => $ex->getMensagem()], $ex->getStatusCode() ?: Response::HTTP_BAD_REQUEST);
        } catch (Exception $ex) {
            return response()->json(["error" => "Erro ao listar todos os registros de acesso. " . $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAcessoRequest $request)
    {
        try {
            // Assumindo que o service retorna stdClass
            $acessoStdClass = $this->service->new(CreateAcessoDTO::makeFromRequest($request)); // Corrigido para $this->service

            $objRetorno = new AcessoObjectResource($acessoStdClass);
            // Retornar o objeto diretamente
            return response()->json($objRetorno->toObject(), Response::HTTP_CREATED); // Usar json() e status correto
        } catch (UsinaWeb_Exception $ex) {
            return response()->json([
                "error" => $ex->getMensagem()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $ex) {
            return response()->json([
                "error" => "Erro ao criar acesso! " . $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Assumindo que o service retorna stdClass ou null
        if (!$acessoStdClass = $this->service->findOne($id)) { // Corrigido para $this->service
            return response()->json(["error" => "Acesso não encontrado!"], Response::HTTP_NOT_FOUND);
        }

        $objRetorno = new AcessoObjectResource($acessoStdClass);
        // Retorna o array diretamente
        return $objRetorno->toObject();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAcessoRequest $request, string $id)
    {
        // 1. Verifica se o acesso existe
        if (!$acesso = $this->service->findOne($id)) {
            return response()->json([
                "error" => "Acesso não encontrado!"
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            // 2. Cria o DTO e tenta atualizar
            $dto = UpdateAcessoDTO::makeFromRequest($request, $id); // Ajustar DTO
            $acessoAtualizado = $this->service->update($dto); // Corrigido para $this->service

            // 3. Verifica se a atualização falhou
            /*if (!$acessoAtualizado) {
                return response()->json([
                    "error" => "Erro ao atualizar acesso!"
                ], Response::HTTP_INTERNAL_SERVER_ERROR); // Ou 404 se o service indicar não encontrado
            }*/

            // 4. Retorna o acesso atualizado
            $objRetorno = new AcessoObjectResource($acessoAtualizado);
            // Retornar o objeto diretamente
            return response()->json($objRetorno->toObject(), Response::HTTP_OK);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json([
                "error" => $ex->getMensagem()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $ex) {
            return response()->json([
                "error" => "Erro ao atualizar acesso! " . $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!$this->service->findOne($id)) {
            return response()->json(["error" => "Acesso não encontrado!"], Response::HTTP_NOT_FOUND);
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
                "error" => "Erro ao deletar acesso! " . $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
