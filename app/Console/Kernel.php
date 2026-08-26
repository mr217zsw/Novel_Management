<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array<int, class-string>
     */
    protected $commands = [
        \App\Console\Commands\CalculateDailyStatistics::class,
        \App\Console\Commands\DailyReconcile::class,
        \App\Console\Commands\CleanupTempFiles::class,
        \App\Console\Commands\SettleAuthors::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 每日对账（每天凌晨 2:00）
        $schedule->command('novel:reconcile')->dailyAt('02:00');

        // 作者分成结算（每天凌晨 3:00）
        $schedule->command('novel:settle-authors')->dailyAt('03:00');

        // 日报统计（每天凌晨 4:00）
        $schedule->command('novel:daily-statistics')->dailyAt('04:00');

        // 清理临时文件（每天凌晨 5:00）
        $schedule->command('novel:cleanup-temp')->dailyAt('05:00');
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
