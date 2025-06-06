<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'jwt.auth' => \Tymon\JWTAuth\Http\Middleware\Authenticate::class,
            // Adicione outros aliases que você possa ter aqui
        ]);

        // Configurar o middleware Authenticate para não redirecionar APIs
        $middleware->redirectGuestsTo(fn ($request) => $request->expectsJson() ? null : route('login'));
        $middleware->redirectUsersTo('/home'); // Manter ou ajustar conforme necessário
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handler para Token Inválido (JWT)
        $exceptions->renderable(function (TokenInvalidException $e, $request) {
            Log::debug('JWT TokenInvalidException capturada.');
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Token inválido.'], 401);
            }
        });

        // Handler para Token Expirado (JWT)
        $exceptions->renderable(function (TokenExpiredException $e, $request) {
            Log::debug('JWT TokenExpiredException capturada.');
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Token expirado.'], 401);
            }
        });

        // Handler para Falta de Autenticação Genérica (ex: sem token)
        $exceptions->renderable(function (AuthenticationException $e, $request) {
            Log::debug('AuthenticationException capturada - Forçando JSON');
            // Remover temporariamente a condição para diagnóstico
            // if ($request->expectsJson()) {
                return response()->json(['message' => 'Não autenticado. (Forçado)'], 401);
            // }
        });

        // Handler Genérico para outras exceções (agora ATIVADO)
        $exceptions->renderable(function (\Throwable $e, $request) {
            // Logar apenas para requisições JSON para não poluir para requisições web
            if ($request->expectsJson()) {
                Log::error('Exceção não tratada capturada para request JSON:', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    // 'trace' => $e->getTraceAsString() // Descomente para trace completo (muito longo)
                ]);
                // Não retornar JSON aqui intencionalmente para vermos o erro 500 padrão,
                // mas o log nos dirá qual foi a exceção.
                // Se quiser retornar um JSON genérico para todos os erros 500 na API:
                // return response()->json(['message' => 'Erro interno do servidor.'], 500);
            }
        });

        // Você pode adicionar outros handlers renderable ou reportable aqui
    })->create();
