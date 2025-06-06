<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;
use App\Models\Acesso;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //Log::info('AuthServiceProvider: Iniciando método boot.');

        try {
            // Tenta uma operação simples para verificar se a conexão com o DB está ativa neste ponto
            DB::connection()->getPdo();
            //Log::info('AuthServiceProvider: Conexão com o banco de dados parece estar OK.');

            $acessos = \App\Models\Acesso::pluck('key')->toArray();

            if (empty($acessos)) {
                //Log::warning('AuthServiceProvider: Nenhuma chave de acesso (permissão) encontrada no banco de dados. Gates não serão definidos dinamicamente a partir do model Acesso.');
            } else {
                //Log::info('AuthServiceProvider: Chaves de acesso encontradas para definir Gates: ' . implode(', ', $acessos));
            }

            foreach ($acessos as $key) {
                if (empty($key)) {
                    //Log::warning('AuthServiceProvider: Encontrada uma chave de acesso vazia no banco. Pulando definição de Gate para esta chave.');
                    continue;
                }
                Gate::define($key, function (Usuario $usuario) use ($key) {
                    //Log::debug("AuthServiceProvider: Verificando Gate '{$key}' para usuário ID {$usuario->id}.");
                    $hasPermission = $usuario->hasPermissionTo($key);
                    //Log::debug("AuthServiceProvider: Resultado da verificação do Gate '{$key}' para usuário ID {$usuario->id}: " . ($hasPermission ? 'CONCEDIDO' : 'NEGADO'));
                    return $hasPermission;
                });
            }
            //Log::info('AuthServiceProvider: Definição dinâmica de Gates concluída (se houveram acessos).');

        } catch (\Throwable $e) {
            /*Log::error('AuthServiceProvider: Exceção CRÍTICA ao tentar buscar acessos ou definir Gates: ' . $e->getMessage(), [
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                // 'trace' => $e->getTraceAsString() // Descomente para trace completo, mas pode gerar logs grandes
            ]);*/
            // Não relance a exceção aqui para que a aplicação continue e possamos ver o erro original do Postman,
            // mas o log nos dirá se o problema começou aqui.
        }

        // Gate para Super Admin (exemplo)
        // \Illuminate\Support\Facades\Gate::before(function (\App\Models\Usuario $usuario, $ability) {
        //     if ($usuario->isSuperAdmin()) { // Supondo um método isSuperAdmin()
        //         return true;
        //     }
        // });
    }
}
