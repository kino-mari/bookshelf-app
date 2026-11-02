<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * レビューの初期データを登録する
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        if ($users->isEmpty() || $books->isEmpty()) {
            $this->command->info('ユーザーまたは書籍が存在しません。先にUserSeederとBookSeederを実行してください。');

            return;
        }

        // 評価別日本語テンプレート（1〜5段階）
        $commentTemplates = [
            5 => [
                '素晴らしい本でした！',
                '人生が変わりました。',
                '何度も読み返しています。',
            ],
            4 => [
                'とても参考になりました。',
                '読みやすくておすすめです。',
                '期待通りの内容でした。',
            ],
            3 => [
                '普通でした。',
                '可もなく不可もなく。',
                '期待したほどではなかった。',
            ],
            2 => [
                '少し期待外れでした。',
                '内容が薄い印象。',
                'もう少し深掘りしてほしかった。',
            ],
            1 => [
                '残念ながら合いませんでした。',
                '期待と違いました。',
            ],
        ];

        // Collectionメソッド（each）を活用した宣言的で可読性の高い実装
        $books->each(function ($book) use ($users, $commentTemplates) {
            // 1. 各書籍のレビュー件数を 2〜4件 でランダム化
            $reviewCount = rand(2, 4);
            $targetCount = min($reviewCount, $users->count());

            // 2. 投稿者をランダムに選出し、コレクションメソッドで処理
            $users->random($targetCount)->each(function ($user) use ($book, $commentTemplates) {
                // 1〜5の評価をランダムに決定（マイ読書レポートのグラフ分布用）
                $rating = rand(1, 5);

                // 対応するテンプレートからコメントをランダムに取得
                $comments = $commentTemplates[$rating];
                $comment = $comments[array_rand($comments)];

                // 重複を防ぐため firstOrCreate を使用
                Review::firstOrCreate(
                    [
                        'book_id' => $book->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'rating' => $rating,
                        'comment' => $comment,
                    ]
                );
            });
        });
    }
}
