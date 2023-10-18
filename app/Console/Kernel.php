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
        // $schedule->command('inspire')->hourly();
        $schedule->command('auth:clear-resets')->everyFifteenMinutes();
        $schedule->command('app:token-lookup')->everyMinute();
        $schedule->command('app:requery-token')->everyTwoMinutes();
        $schedule->command('app:pospaid-lookup')->everyMinute();

        // Enable task scheduler logging
         $schedule->exec('echo "Task Scheduler Ran: $(date)" >> /var/www/html/IBEDCENGINE/storage/logs/scheduler.log')->everyMinute();
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
