<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
// use App\Console\Commands\PromotionStartCommand;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        $schedule->command('promotion:schedule')->dailyAt('1:00');

        $schedule->command('slideshow:schedule')->dailyAt('1:00');
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
