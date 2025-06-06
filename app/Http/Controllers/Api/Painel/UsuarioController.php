<?php

namespace App\Http\Controllers\Api\Painel;

use App\Http\Controllers\Controller;
use App\Http\Util\UsinaWeb_Exception;
use Exception;
use Illuminate\Http\Request;
use App\Services\UsuarioService;
use App\DTO\CreateUsuarioDTO;
use App\DTO\UpdateUsuarioDTO;
use App\Http\Resources\UsuarioResource;
use App\Http\Resources\Object\UsuarioObjectResource;
use App\Adapters\ApiAdapter;

use Illuminate\Http\Response;
// Requests
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;

class UsuarioController extends Controller
{
    public function __construct(
        protected UsuarioService $service
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $usuarios = $this->service->paginate(
            page: $request->get('page', 1),
            totalPerPage: $request->get('per_page', 15),
            filter: $request->filter,
        );

        return UsuarioResource::collection($usuarios->items())
            ->additional([
                'meta' => ApiAdapter::pagination($usuarios),
            ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUsuarioRequest $request)
    {
        try {
            $usuario = $this->service->new(CreateUsuarioDTO::makeFromRequest($request));
            $objRetorno = new UsuarioObjectResource($usuario);
            return response()->json($objRetorno->toObject(), Response::HTTP_CREATED);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json([
                "error" => $ex->getMensagem()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $ex) {
            return response()->json([
                "error" => "Erro ao criar usuário! " . $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (!$usuario = $this->service->findOne($id)) {
            return response()->json(["error" => "Usuário não encontrado!"], Response::HTTP_NOT_FOUND);
        }

        $objRetorno = new UsuarioObjectResource($usuario);

        return $objRetorno->toObject();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUsuarioRequest $request, string $id)
    {
        // 1. Verifica se o usuário existe
        if (!$usuario = $this->service->findOne($id)) {
            return response()->json([
                "error" => "Usuário não encontrado!"
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            // 2. Cria o DTO e tenta atualizar
            $dto = UpdateUsuarioDTO::makeFromRequest($request, $id);
            $usuarioAtualizado = $this->service->update($dto);

            // 3. Verifica se a atualização falhou
            /*if (!$usuarioAtualizado) {
                return response()->json([
                    "error" => "Erro ao atualizar usuário!"
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }*/

            // 4. Retorna o usuário atualizado
            $objRetorno = new UsuarioObjectResource($usuarioAtualizado);
            return response()->json($objRetorno->toObject(), Response::HTTP_OK);
        } catch (UsinaWeb_Exception $ex) {
            return response()->json([
                "error" => $ex->getMensagem()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $ex) {
            return response()->json([
                "error" => "Erro ao atualizar usuário! " . $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!$this->service->findOne($id)) {
            return response()->json(["error" => "Usuário não encontrado!"], Response::HTTP_NOT_FOUND);
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
                "error" => "Erro ao deletar usuário! " . $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Verifica se um login já existe no sistema.
     */
    public function checkLogin(Request $request)
    {
        $login = $request->input('login');

        if (!$login) {
            return response()->json([
                "error" => "O parâmetro 'login' é obrigatório!"
            ], Response::HTTP_BAD_REQUEST);
        }

        $exists = $this->service->loginExists($login);

        return response()->json([
            "exists" => $exists,
            "message" => $exists ? "Login já existe no sistema." : "Login disponível."
        ], Response::HTTP_OK);
    }
}
