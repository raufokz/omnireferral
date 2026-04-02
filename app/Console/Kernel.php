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
        // Send lead follow-up reminders daily at 9 AM
        // Note: To enable scheduling, run 'php artisan schedule:work' or set up cron:
        // * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
        $schedule->command('lead:follow-up-reminders')->dailyAt('09:00');
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
