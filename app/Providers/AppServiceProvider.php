<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\{
    EventoService,
    ParticipanteService,
    InscricaoService,
    EsperaService,
    MercadoPagoService,
    UsuarioService,
    AcessoService,
    ConfiguracaoService,
    GrupoService,
    GrupoUsuarioService,
    AcessoGrupoService
};
use App\Repositories\Contracts\{
    EventoRepositoryInterface,
    ParticipanteRepositoryInterface,
    InscricaoRepositoryInterface,
    EsperaRepositoryInterface,
    UsuarioRepositoryInterface,
    AcessoRepositoryInterface,
    ConfiguracaoRepositoryInterface,
    GrupoRepositoryInterface,
    GrupoUsuarioRepositoryInterface,
    AcessoGrupoRepositoryInterface,
    PagamentoPendenteRepositoryInterface
};
use App\Repositories\Eloquent\{
    EventoRepository,
    ParticipanteRepository,
    InscricaoRepository,
    EsperaRepository,
    UsuarioRepository,
    AcessoRepository,
    ConfiguracaoRepository,
    GrupoRepository,
    GrupoUsuarioRepository,
    AcessoGrupoRepository,
    PagamentoPendenteRepository
};

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            EventoRepositoryInterface::class,
            EventoRepository::class
        );
        $this->app->bind(
            ParticipanteRepositoryInterface::class,
            ParticipanteRepository::class
        );
        $this->app->bind(
            InscricaoRepositoryInterface::class,
            InscricaoRepository::class
        );
        $this->app->bind(
            EsperaRepositoryInterface::class,
            EsperaRepository::class
        );
        $this->app->bind(
            UsuarioRepositoryInterface::class,
            UsuarioRepository::class
        );
        $this->app->bind(
            AcessoRepositoryInterface::class,
            AcessoRepository::class
        );
        $this->app->bind(
            ConfiguracaoRepositoryInterface::class,
            ConfiguracaoRepository::class
        );
        $this->app->bind(
            GrupoRepositoryInterface::class,
            GrupoRepository::class
        );
        $this->app->bind(
            GrupoUsuarioRepositoryInterface::class,
            GrupoUsuarioRepository::class
        );
        $this->app->bind(
            AcessoGrupoRepositoryInterface::class,
            AcessoGrupoRepository::class
        );
        $this->app->bind(
            PagamentoPendenteRepositoryInterface::class,
            PagamentoPendenteRepository::class
        );

        // Bind Services
        $this->app->singleton(EventoService::class, function ($app) {
            return new EventoService($app->make(EventoRepositoryInterface::class));
        });
        $this->app->singleton(ParticipanteService::class, function ($app) {
            return new ParticipanteService($app->make(ParticipanteRepositoryInterface::class));
        });
        $this->app->singleton(InscricaoService::class, function ($app) {
            return new InscricaoService(
                $app->make(InscricaoRepositoryInterface::class),
                $app->make(EventoRepositoryInterface::class)
            );
        });
        $this->app->singleton(EsperaService::class, function ($app) {
            return new EsperaService($app->make(EsperaRepositoryInterface::class));
        });
        $this->app->singleton(MercadoPagoService::class, function ($app) {
            return new MercadoPagoService(
                $app->make(InscricaoService::class),
                $app->make(EsperaService::class)
            );
        });
        $this->app->singleton(UsuarioService::class, function ($app) {
            return new UsuarioService($app->make(UsuarioRepositoryInterface::class));
        });
        $this->app->singleton(AcessoService::class, function ($app) {
            return new AcessoService($app->make(AcessoRepositoryInterface::class));
        });
        $this->app->singleton(ConfiguracaoService::class, function ($app) {
            return new ConfiguracaoService($app->make(ConfiguracaoRepositoryInterface::class));
        });
        $this->app->singleton(GrupoService::class, function ($app) {
            return new GrupoService($app->make(GrupoRepositoryInterface::class));
        });
        $this->app->singleton(GrupoUsuarioService::class, function ($app) {
            return new GrupoUsuarioService($app->make(GrupoUsuarioRepositoryInterface::class));
        });
        $this->app->singleton(AcessoGrupoService::class, function ($app) {
            return new AcessoGrupoService($app->make(AcessoGrupoRepositoryInterface::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
