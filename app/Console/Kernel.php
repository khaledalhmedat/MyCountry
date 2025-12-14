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
        // نسخ احتياطي أسبوعي كل يوم أحد الساعة 2:00 صباحاً
        $schedule->command('db:backup')
            ->weekly()
            ->sundays()
            ->at('02:00')
            ->timezone('Asia/Riyadh')
            ->appendOutputTo(storage_path('logs/backup.log'));
        
        // إضافة نسخ يومية للجدول المهمة فقط
        $schedule->command('db:backup --tables=complaints,users')
            ->daily()
            ->at('01:00')
            ->timezone('Asia/Riyadh');
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