<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminder;

class ProcessReadingPlans extends Command
{
    protected $signature = 'reading-plans:process';

    protected $description = '読書計画の期限チェックとリマインダー通知を実行する';

    public function handle()
    {
        $this->info('読書計画の処理を開始します。');

        // ① 期限を過ぎた未着手・進行中の計画を期限切れに変更
        ReadingPlan::whereDate('due_date', '<', today())
            ->whereIn('status', [0, 1])
            ->update(['status' => 3]);

        // ② 明日が期限の未着手・進行中の計画を取得
        $plans = ReadingPlan::with(['user', 'book'])
            ->whereDate('due_date', today()->addDay())
            ->whereIn('status', [0, 1])
            ->get();

        foreach ($plans as $plan) {

            // ③ 同じ計画への通知が既に存在するか確認
            $alreadyNotified = $plan->user
                ->notifications()
                ->where('type', ReadingPlanReminder::class)
                ->where('data->reading_plan_id', $plan->id)
                ->exists();

            // ④ 通知済みでなければ通知を送る
            if (!$alreadyNotified) {
                $plan->user->notify(
                    new ReadingPlanReminder($plan)
                );
            }
        }

        $this->info('読書計画の処理が完了しました。');

        return Command::SUCCESS;
    }
}