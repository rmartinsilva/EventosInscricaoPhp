<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Painel\AuthController;
use App\Http\Controllers\Api\Painel\UsuarioController;
use App\Http\Controllers\Api\Painel\GrupoController;
use App\Http\Controllers\Api\Painel\AcessoController;
use App\Http\Controllers\Api\Painel\GrupoUsuarioController;
use App\Http\Controllers\Api\Painel\AcessoGrupoController;
use App\Http\Controllers\Api\Painel\ConfiguracaoController;
use App\Http\Controllers\Api\Painel\EventoController;
use App\Http\Controllers\Api\Painel\InscricaoController as PainelInscricaoController;
use App\Http\Controllers\Api\Site\InscricaoController;
use App\Http\Controllers\Api\Site\ParticipanteController;
use App\Http\Controllers\Api\Site\PagamentoController;
use App\Http\Controllers\Api\Site\EsperaController;
use Illuminate\Support\Facades\Artisan;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('painel')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::middleware('jwt.auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::post('me', [AuthController::class, 'me']);
        });
    });

    Route::middleware('jwt.auth')->group(function () {
        // IMPORTANTE: Rotas com caminhos fixos devem vir ANTES das rotas com parâmetros dinâmicos
        // para evitar que o Laravel interprete o caminho fixo como um parâmetro dinâmico
        Route::controller(UsuarioController::class)->group(function () {
            Route::get('usuarios/check-login', 'checkLogin')->middleware('can:create_usuarios');
            Route::get('usuarios', 'index')->middleware('can:view_usuarios');
            Route::post('usuarios', 'store')->middleware('can:create_usuarios');
            Route::get('usuarios/{usuario}', 'show')->middleware('can:view_usuarios');
            Route::put('usuarios/{usuario}', 'update')->middleware('can:update_usuarios');
            Route::delete('usuarios/{usuario}', 'destroy')->middleware('can:delete_usuarios');
        });

        Route::controller(GrupoController::class)->group(function () {
            Route::get('grupos', 'index')->middleware('can:view_grupos');
            Route::post('grupos', 'store')->middleware('can:create_grupos');
            Route::get('grupos/all', 'getAll')->name('grupos.getAll')->middleware('can:view_grupos');
            Route::get('grupos/{grupo}', 'show')->middleware('can:view_grupos');
            Route::put('grupos/{grupo}', 'update')->middleware('can:update_grupos');
            Route::delete('grupos/{grupo}', 'destroy')->middleware('can:delete_grupos');
            Route::post('grupos/{grupo}/sync-acessos', 'syncAcessos')->name('grupos.syncAcessos')->middleware('can:manage_grupo_acessos');
            Route::post('grupos/{grupo}/sync-usuarios', 'syncUsuarios')->name('grupos.syncUsuarios')->middleware('can:manage_grupo_usuarios');
        });

        Route::controller(AcessoController::class)->group(function () {
            Route::get('acessos', 'index')->middleware('can:view_acessos');
            Route::get('acessos/all', 'getAll')->name('acessos.getAll')->middleware('can:view_acessos');
            Route::post('acessos', 'store')->middleware('can:create_acessos');
            Route::get('acessos/{acesso}', 'show')->middleware('can:view_acessos');
            Route::put('acessos/{acesso}', 'update')->middleware('can:update_acessos');
            Route::delete('acessos/{acesso}', 'destroy')->middleware('can:delete_acessos');
        });

        Route::controller(GrupoUsuarioController::class)->prefix('grupo-usuario')->group(function () {
            Route::get('/', 'index')->middleware('can:view_grupo_usuario');
            Route::post('/', 'store')->middleware('can:create_grupo_usuario');
            Route::get('/all', 'getAll')->name('grupo-usuario.getAll')->middleware('can:view_grupo_usuario');
            Route::get('/grupos-disponiveis/{usuario_id}', 'getGruposDisponiveis')->name('grupo-usuario.grupos-disponiveis')->middleware('can:view_grupo_usuario');
            Route::get('/{grupoUsuario}', 'show')->middleware('can:view_grupo_usuario');
            Route::put('/{grupoUsuario}', 'update')->middleware('can:update_grupo_usuario');
            Route::delete('/{grupoUsuario}', 'destroy')->middleware('can:delete_grupo_usuario');
        });
        
        Route::controller(AcessoGrupoController::class)->prefix('acesso-grupo')->group(function () {
            Route::get('/', 'index')->middleware('can:view_acesso_grupo');
            Route::post('/', 'store')->middleware('can:create_acesso_grupo');
            Route::get('/all', 'getAll')->name('acesso-grupo.getAll')->middleware('can:view_acesso_grupo');
            Route::get('/acessos-disponiveis/{grupo_id}', 'getAcessosDisponiveisParaGrupo')->name('acesso-grupo.acessosDisponiveis')->middleware('can:view_acesso_grupo');
            Route::get('/acessos-bygrupo/{grupo_id}', 'findByGrupo')->name('acesso-grupo.acessosByGrupo')->middleware('can:view_acesso_grupo');
            Route::post('/{grupo_id}/sync', 'syncAcessosGrupo')->name('painel.acesso-grupo.sync');
            Route::get('/{acessoGrupo}', 'show')->middleware('can:view_acesso_grupo');
            Route::put('/{acessoGrupo}', 'update')->middleware('can:update_acesso_grupo');
            Route::delete('/{acessoGrupo}', 'destroy')->middleware('can:delete_acesso_grupo');
        });

        Route::controller(ConfiguracaoController::class)->group(function () {
            Route::get('configuracoes', 'index')->middleware('can:view_configuracoes');
            Route::post('configuracoes', 'store')->middleware('can:create_configuracoes');
            Route::get('configuracoes/{configuracao}', 'show')->middleware('can:view_configuracoes');
            Route::put('configuracoes/{configuracao}', 'update')->middleware('can:update_configuracoes');
            Route::delete('configuracoes/{configuracao}', 'destroy')->middleware('can:delete_configuracoes');
        });
        
        Route::controller(EventoController::class)->group(function () {
            Route::get('eventos', 'index')->middleware('can:view_eventos');
            Route::post('eventos', 'store')->middleware('can:create_eventos');
            Route::get('eventos/ativos', 'getAllAtivos')->middleware('can:view_eventos');
            Route::get('eventos/all', 'getAll')->middleware('can:view_eventos');
            Route::get('eventos/{evento}', 'show')->middleware('can:view_eventos');
            Route::put('eventos/{evento}', 'update')->middleware('can:update_eventos');
            Route::delete('eventos/{evento}', 'destroy')->middleware('can:delete_eventos');
            
        });

        Route::controller(PainelInscricaoController::class)->group(function () {
            Route::get('inscricoes', 'index')->middleware('can:view_inscricoes');
            Route::get('inscricoes/all', 'getAll')->middleware('can:view_inscricoes');
            Route::post('inscricoes', 'store')->middleware('can:create_inscricoes');
            Route::get('inscricoes/count/cortesia/evento/{eventoCodigo}', [PainelInscricaoController::class, 'getCountCortesiaByEvento']);
            Route::get('inscricoes/evento/{eventoCodigo}', 'getAllByEvento')->middleware('can:view_inscricoes');
            Route::get('inscricoes/{inscricao}', 'show')->middleware('can:view_inscricoes');
            Route::put('inscricoes/{inscricao}', 'update')->middleware('can:update_inscricoes');
            Route::delete('inscricoes/{inscricao}', 'destroy')->middleware('can:delete_inscricoes');
            
        });

    });
});

Route::controller(ParticipanteController::class)->group(function () {
    Route::get('participantes', 'index');
    Route::post('participantes', 'store');
    Route::get('participantes/cpf/search', 'searchByCpf');
    Route::get('participantes/{participante}', 'show');
    Route::put('participantes/{participante}', 'update');
    
    //Route::delete('participantes/{participante}', 'destroy');
});

Route::controller(EventoController::class)->group(function () {
    Route::get('evento/{url}', 'showByUrl')->name('eventos.showByUrl');
});

Route::controller(ConfiguracaoController::class)->group(function () {    
    Route::get('configuracoes/{configuracao}', 'show');    
});

  // Rotas para Inscricoes
  Route::controller(InscricaoController::class)->group(function () {
    Route::get('inscricoes', 'index');
    Route::post('inscricoes', 'store');
    Route::get('inscricoes/all', 'getAll');     
    Route::get('inscricoes/participante/{participante}/evento/{evento}', 'getByParticipanteEvento');
    Route::get('inscricoes/count/evento/{eventoCodigo}', [InscricaoController::class, 'getCountPagasByEvento']);    
    Route::get('inscricoes/count/evento/{eventoCodigo}', [InscricaoController::class, 'getCountPagasByEvento']);
    Route::get('inscricoes/{inscricao}', 'show'); // {inscricao} é o padrão para model binding
    Route::put('inscricoes/{inscricao}', 'update');
    Route::delete('inscricoes/{inscricao}', 'destroy');
});


Route::controller(PagamentoController::class)->group(function () {

    Route::post('/pagamentos/cartao', [PagamentoController::class, 'processarCartao'])->name('pagamentos.cartao');
    Route::post('/pagamentos/pix', [PagamentoController::class, 'processarPix'])->name('pagamentos.pix');
    
    // Rota para webhook do Mercado Pago
    Route::post('/webhooks/mercadopago', [PagamentoController::class, 'handleMercadoPagoWebhook'])->name('webhooks.mercadopago');
    
    // Rota para verificar status do pagamento pelo ID do Mercado Pago
    Route::get('/pagamentos/status/mp/{id_pagamento_mp}', [PagamentoController::class, 'verificarStatusPagamentoMp'])->name('pagamentos.status-mp');
    

});

/*
Route::get('/run-verificar-pagamentos', function () {
    // Adicione autenticação/autorização aqui para proteger este endpoint!
    // Exemplo: if (auth()->user()->isAdmin()) { ... }

    try {
        Artisan::call('mercadopago:verificar-pagamentos');
        $output = Artisan::output();
        return response()->json(['status' => 'success', 'output' => $output]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
});
*/

Route::controller(EsperaController::class)->group(function () {
    
    
    Route::get('/esperas/all', [EsperaController::class, 'getAll']);
    Route::get('/esperas/participante/{participante}/evento/{evento}', [EsperaController::class, 'getByParticipanteEvento']);
    Route::apiResource('/esperas', EsperaController::class);
});

