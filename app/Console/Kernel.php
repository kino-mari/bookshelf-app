<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * アプリケーションのコマンド実行スケジュールを定義する
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('reading-plans:process')->daily();
    }

    /**
     * アプリケーションのコマンドを登録する
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
