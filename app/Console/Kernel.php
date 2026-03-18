<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Moras y avisos: corre diariamente al mediodía
        $schedule->command('aguateria:aplicar-moras')
                 ->dailyAt('12:00')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/moras.log'));

        // Facturación automática: corre cada minuto
        // El comando internamente verifica si hoy es el día Y hora configurados por empresa
        $schedule->command('aguateria:facturacion-automatica')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/facturacion-automatica.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
