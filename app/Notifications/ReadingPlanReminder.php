<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // キューを使う場合に必要
use Illuminate\Notifications\Notification;
use App\Models\ReadingPlan;

class ReadingPlanReminder extends Notification implements ShouldQueue // ← implements ShouldQueue を追加
{
    use Queueable;

    protected $readingPlan;

    // コンストラクタで対象の読書計画を受け取る
    public function __construct(ReadingPlan $readingPlan)
    {
        $this->readingPlan = $readingPlan;
    }

    // どのチャンネルを使って通知するか
    public function via($notifiable): array
    {
        return ['database'];
    }

    // データベースに保存する内容（toArrayを使用）
    public function toArray($notifiable): array
    {
        return [
            'reading_plan_id' => $this->readingPlan->id,
            'book_title' => $this->readingPlan->book->title,
            'due_date' => $this->readingPlan->due_date,
            'message' => "「{$this->readingPlan->book->title}」の読書期限（{$this->readingPlan->due_date}）が近づいています！",
        ];
    }
}