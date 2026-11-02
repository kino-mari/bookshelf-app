<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReadingPlan;

class ProcessReadingPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reading-plans:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '読書計画の期限チェックとリマインダー通知を実行する';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('読書計画の処理を開始します。');

        // 期日を過ぎた未着手・進行中の計画を期限切れに変更
        ReadingPlan::whereDate('due_date', '<', today())
            ->whereIn('status', [0, 1])
            ->update(['status' => 3]);

        $this->info('読書計画の処理が完了しました。');
    }
}
