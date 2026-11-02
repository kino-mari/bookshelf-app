<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Notifications\Notification;

class ReadingPlanReminder extends Notification
{
    protected ReadingPlan $readingPlan;

    /**
     * 通知対象の読書計画を受け取る
     *
     * @param  ReadingPlan  $readingPlan  通知対象の読書計画
     */
    public function __construct(ReadingPlan $readingPlan)
    {
        $this->readingPlan = $readingPlan;
    }

    /**
     * 通知に使用するチャンネルを取得する
     *
     * @return array 通知チャンネル
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * データベースに保存する通知データを取得する
     *
     * @return array 通知データ
     */
    public function toArray($notifiable): array
    {
        $bookTitle = $this->readingPlan->book->title;
        $dueDate = $this->readingPlan->due_date->format('Y-m-d');

        return [
            'reading_plan_id' => $this->readingPlan->id,
            'book_title' => $bookTitle,
            'due_date' => $dueDate,
            'message' => "「{$bookTitle}」の読書期限（{$dueDate}）が近づいています！",
        ];
    }
}
