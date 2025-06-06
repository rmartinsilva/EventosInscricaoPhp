<?php
namespace App\Http\Util;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\RecordsNotFoundException;
use PDOException;
use Exception;

class UsinaWeb_Exception extends Exception {

    private $mensagem;
    private $statusCode;

    /**
     * @return mixed
     */
    public function getMensagem()
    {
        return $this->mensagem;
    }

    /**
     * @param mixed $mensagem
     */
    public function setMensagem($mensagem)
    {
        $this->mensagem = $mensagem;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    public function __construct($erro){
        parent::__construct($erro instanceof Exception ? $erro->getMessage() : 'Erro na aplicação');

        $this->statusCode = 500; // Status padrão para erros internos

        switch (true){
            // Violações de Constraints do banco de dados
            case $erro instanceof \Illuminate\Database\UniqueConstraintViolationException:
                $this->setMensagem("Esse registro já existe no sistema.");
                $this->statusCode = 409; // Conflict
                break;

            case $erro instanceof QueryException:
                // Verificar códigos de erro específicos do PDO/MySQL
                if (isset($erro->errorInfo[1])) {
                    switch ($erro->errorInfo[1]) {
                        // Unique constraint
                        case 1062:
                            $this->setMensagem("Esse registro já existe no sistema.");
                            $this->statusCode = 409; // Conflict
                            break;
                        
                        // Foreign Key constraint
                        case 1451:
                            $this->setMensagem("Não é possível excluir este registro porque ele está sendo usado em outras partes do sistema.");
                            $this->statusCode = 409; // Conflict
                            break;
                        
                        case 1452:
                            $this->setMensagem("Um dos campos informados faz referência a um registro que não existe.");
                            $this->statusCode = 422; // Unprocessable Entity
                            break;

                        // Max length
                        case 1406:
                            $this->setMensagem("Um dos campos contém mais caracteres do que o permitido.");
                            $this->statusCode = 422; // Unprocessable Entity
                            break;
                        
                        // Not null constraint
                        case 1048:
                            $this->setMensagem("Um campo obrigatório não foi informado.");
                            $this->statusCode = 422; // Unprocessable Entity
                            break;
                        
                        // Deadlock
                        case 1213:
                            $this->setMensagem("Ocorreu um conflito no banco de dados. Por favor, tente novamente.");
                            break;
                        
                        // Timeout
                        case 2006:
                        case 2013:
                            $this->setMensagem("A conexão com o banco de dados expirou. Por favor, tente novamente.");
                            break;
                            
                        default:
                            $this->setMensagem("Erro ao processar a solicitação no banco de dados. Por favor, tente novamente mais tarde.");
                    }
                } else {
                    $this->setMensagem("Erro ao processar a solicitação no banco de dados. Por favor, tente novamente mais tarde.");
                }
                break;

            // Erro de modelo não encontrado
            case $erro instanceof ModelNotFoundException:
                $model = class_basename($erro->getModel());
                $this->setMensagem("O registro solicitado não foi encontrado.");
                $this->statusCode = 404; // Not Found
                break;
                
            // Rota não encontrada
            case $erro instanceof NotFoundHttpException:
                $this->setMensagem("A página ou recurso solicitado não existe.");
                $this->statusCode = 404; // Not Found
                break;
                
            // Método HTTP não permitido
            case $erro instanceof MethodNotAllowedHttpException:
                $this->setMensagem("O método de requisição não é permitido para esta rota.");
                $this->statusCode = 405; // Method Not Allowed
                break;
                
            // Erro de validação
            case $erro instanceof ValidationException:
                $this->setMensagem($erro->validator->errors()->first());
                $this->statusCode = 422; // Unprocessable Entity
                break;
                
            // Erro de autenticação
            case $erro instanceof AuthenticationException:
                $this->setMensagem("Você precisa estar autenticado para acessar este recurso.");
                $this->statusCode = 401; // Unauthorized
                break;
                
            // Erro de autorização
            case $erro instanceof AuthorizationException:
                $this->setMensagem("Você não tem permissão para acessar este recurso.");
                $this->statusCode = 403; // Forbidden
                break;
                
            // Erro de registros não encontrados
            case $erro instanceof RecordsNotFoundException:
                $this->setMensagem("Os registros solicitados não foram encontrados.");
                $this->statusCode = 404; // Not Found
                break;
                
            // Erro de conexão PDO
            case $erro instanceof PDOException:
                $this->setMensagem("Erro de conexão com o banco de dados. Por favor, tente novamente mais tarde.");
                break;
                
            // Erro HTTP genérico
            case $erro instanceof HttpException:
                $this->setMensagem($erro->getMessage());
                $this->statusCode = $erro->getStatusCode();
                break;

            // Códigos de erro específicos (mantidos do código original)
            case "1022":
                $this->setMensagem("Não foi possível criar o registro devido a uma duplicidade.");
                $this->statusCode = 409; // Conflict
                break;

            case "1408":
                $this->setMensagem("Um campo obrigatório não pode ser nulo.");
                $this->statusCode = 422; // Unprocessable Entity
                break;
                
            // Erro genérico
            default:
                // Em produção, não mostrar detalhes técnicos
                if (config('app.debug')) {
                    $this->setMensagem("Erro ao processar sua solicitação: " . $erro->getMessage());
                } else {
                    $this->setMensagem("Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente mais tarde.");
                }
        }
    }
} 