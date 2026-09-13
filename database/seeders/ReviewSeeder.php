<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 全ユーザーと全書籍を取得
        $users = User::all();
        $books = Book::all();

        // 投稿文のサンプルテンプレート
        $comments = [
            5 => [
                '非常に読みやすく、一気に読破しました。人生のバイブルにしたいと思います！',
                '期待以上の内容でした。何度も読み返して復習したい一冊です。',
                '説明が非常に丁寧で、初心者でもスムーズに理解することができました。',
                '素晴らしい内容でした！友人や同僚にも自信を持っておすすめできます。',
            ],
            4 => [
                'とても勉強になりました。実践的な内容が多く、すぐに役に立ちそうです。',
                '全体的に分かりやすくまとまっています。買って損はない一冊だと思います。',
                '著者の視点が鋭く、新しい発見がたくさんありました。',
                '読み応えがありました。少し難解な部分もありましたが満足度は高いです。',
            ],
            3 => [
                '可もなく不可もなく、標準的な内容でした。基礎知識のおさらいには良さそうです。',
                '書いてある内容は良いですが、もう少し具体的な事例があると嬉しかったです。',
                '参考になる部分もありましたが、すでに知っている情報も多めでした。',
                'サラッと読める内容です。暇つぶしや導入本としてはアリだと思います。',
            ],
        ];

        // 各書籍（11冊）に対するレビュー件数の配分（合計32件）
        // 書籍1~11: 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 2 件
        $reviewCountsPerBook = [3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 2];

        $reviewIndex = 0;

        foreach ($books as $bookIndex => $book) {
            $count = $reviewCountsPerBook[$bookIndex] ?? 2;

            for ($i = 0; $i < $count; $i++) {
                // 5人のユーザーを順番に割り当て（重複を避けて万遍なく配置）
                $user = $users[$reviewIndex % $users->count()];

                // レーティング（3〜5）を順番に決定
                $rating = [5, 4, 3, 5, 4][($reviewIndex + $i) % 5];

                // コメントを取得
                $commentList = $comments[$rating];
                $comment = $commentList[$reviewIndex % count($commentList)];

                Review::create([
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                    'rating' => $rating,
                    'comment' => $comment,
                ]);

                $reviewIndex++;
            }
        }
    }
}