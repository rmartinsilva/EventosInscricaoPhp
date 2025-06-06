<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\MercadoPagoVerificarPagamentos;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Seus outros comandos
        MercadoPagoVerificarPagamentos::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('inspire')->everyMinute(); // Teste com comando embutido
        // $schedule->command('mercadopago:verificar-pagamentos')->everyMinute();//hourly();
        // Você pode ajustar a frequência conforme necessário: daily(), everyThirtyMinutes(), etc.
        // Exemplo com log de saída:
        // $schedule->command('mercadopago:verificar-pagamentos')
        //          ->hourly()
        //          ->sendOutputTo(storage_path('logs/mercadopago_verificar.log'))
        //          ->emailOutputOnFailure('seu-email@example.com');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        // require base_path('routes/console.php'); // Temporariamente comentado para teste
    }
} 