<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Notifications\ReadingPlanReminder;
use Illuminate\Support\Facades\DB;

class ProcessReadingPlansTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 期限切れの読書計画だけを期限切れ状態に変更する
     */
    public function test_process_reading_plans_updates_expired_plans(): void
    {
        $user = User::factory()->create();

        // 期限切れ・未着手
        $bookA = Book::factory()->create(['user_id' => $user->id]);
        $planA = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $bookA->id,
            'due_date' => today()->subDay(),
            'status' => 0,
        ]);

        // 期限切れ・進行中
        $bookB = Book::factory()->create(['user_id' => $user->id]);
        $planB = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $bookB->id,
            'due_date' => today()->subDay(),
            'status' => 1,
        ]);

        // 期限前・未着手
        $bookC = Book::factory()->create(['user_id' => $user->id]);
        $planC = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $bookC->id,
            'due_date' => today()->addDay(),
            'status' => 0,
        ]);

        // 期限切れ・読了済み
        $bookD = Book::factory()->create(['user_id' => $user->id]);
        $planD = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $bookD->id,
            'due_date' => today()->subDay(),
            'status' => 2,
        ]);

        // バッチ処理を実行
        $this->artisan('reading-plans:process')
            ->assertExitCode(0);

        // 実行後のステータスを確認
        $this->assertSame(3, $planA->fresh()->getRawOriginal('status'));
        $this->assertSame(3, $planB->fresh()->getRawOriginal('status'));
        $this->assertSame(0, $planC->fresh()->getRawOriginal('status'));
        $this->assertSame(2, $planD->fresh()->getRawOriginal('status'));
    }

    /**
     * 明日が期限の読書計画にリマインダー通知を送り、二重通知を防ぐ
     */
    public function test_process_reading_plans_sends_reminder_only_once(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $plan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'due_date' => today()->addDay(),
            'status' => 0,
        ]);

        // 1回目のバッチ処理
        $this->artisan('reading-plans:process')
            ->assertExitCode(0);

        // 通知が1件保存されたことを確認
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
            'type' => ReadingPlanReminder::class,
        ]);

        $notificationCount = DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->where('type', ReadingPlanReminder::class)
            ->count();

        $this->assertSame(1, $notificationCount);

        // 2回目のバッチ処理
        $this->artisan('reading-plans:process')
            ->assertExitCode(0);

        // 2回実行しても通知は1件のまま
        $notificationCount = DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->where('type', ReadingPlanReminder::class)
            ->count();

        $this->assertSame(1, $notificationCount);
    }
}