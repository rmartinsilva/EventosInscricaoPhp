<?php

namespace App\Providers;

use App\Models\Configuracao;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class MercadoPagoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        try {
            if (Schema::hasTable('configuracoes')) {
                $configuracao = Configuracao::first();

                if ($configuracao) {
                    config([
                        'mercadopago.public_key' => $configuracao->chave_api,
                        'mercadopago.access_token' => $configuracao->token_api,
                        'mercadopago.webhook_secret' => $configuracao->webhooksecret,
                        'mercadopago.notification_url' => $configuracao->notificationurl,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::info('Não foi possível carregar as configurações do MercadoPago do banco de dados: ' . $e->getMessage());
        }
    }
}
