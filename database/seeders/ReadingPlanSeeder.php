<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReadingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $yamada = User::where('email', 'yamada@example.com')->first();
        $suzuki = User::where('email', 'suzuki@example.com')->first();

        if (!$yamada || !$suzuki) {
            $this->command->info('山田太郎または鈴木花子が存在しません。先にUserSeederを実行してください。');
            return;
        }

        $books = Book::take(6)->get();

        if ($books->count() < 6) {
            $this->command->info('書籍データが6件以上必要です。先にBookSeederを実行してください。');
            return;
        }

        // コメント（status: 0=未着手, 1=進行中, 2=完了）
        // 1. 山田太郎：3日前リマインダー対象 (進行中 = 1)
        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[0]->id,
            'due_date' => Carbon::today()->addDays(3),
            'status' => 1,
        ]);

        // 2. 山田太郎：当日リマインダー対象 (進行中 = 1)
        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[1]->id,
            'due_date' => Carbon::today(),
            'status' => 1,
        ]);

        // 3. 山田太郎：Auto-expire 化 + 3日後再エンゲージメント対象 (進行中 = 1)
        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[2]->id,
            'due_date' => Carbon::today()->subDays(3),
            'status' => 1,
        ]);

        // 4. 山田太郎：リマインダー対象外 (進行中 = 1)
        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[3]->id,
            'due_date' => Carbon::today()->addDays(7),
            'status' => 1,
        ]);

        // 5. 山田太郎：完了済み (完了 = 2)
        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[4]->id,
            'due_date' => Carbon::today()->subDays(10),
            'status' => 2,
            'completed_at' => Carbon::today()->subDays(5),
        ]);

        // 6. 鈴木花子：他ユーザー認可テスト用 (進行中 = 1)
        ReadingPlan::create([
            'user_id' => $suzuki->id,
            'book_id' => $books[5]->id,
            'due_date' => Carbon::today()->addDays(5),
            'status' => 1,
        ]);
    }
}