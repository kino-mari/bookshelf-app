<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ログインユーザーの読書レポートが正しく集計される
     */
    public function test_user_can_view_own_reading_report(): void
    {
        // ログインユーザーを作成
        $user = User::factory()->create();

        // 別のユーザーを作成
        $otherUser = User::factory()->create();

        // 書籍を2冊作成
        $bookA = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'Laravel入門',
        ]);

        $bookB = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'PHP入門',
        ]);

        // ジャンルを2つ作成
        $technicalGenre = Genre::factory()->create([
            'name' => '技術書',
        ]);

        $novelGenre = Genre::factory()->create([
            'name' => '小説',
        ]);

        // 書籍にジャンルを紐づける
        $bookA->genres()->attach($technicalGenre->id);
        $bookB->genres()->attach($novelGenre->id);

        // ログインユーザーのレビューを3件作成
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $bookA->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $bookA->id,
            'rating' => 3,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $bookB->id,
            'rating' => 4,
        ]);

        // 別ユーザーのレビューを1件作成
        Review::factory()->create([
            'user_id' => $otherUser->id,
            'rating' => 1,
        ]);

        // ログインしてレポート画面にアクセス
        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        // 正常に画面が表示される
        $response->assertOk();

        // レポートの集計結果を確認
        $response->assertViewHas('stats', function ($stats) use ($bookA, $bookB) {

            // 基本サマリー
            if ($stats['summary']['total_reviews'] !== 3) {
                return false;
            }

            if ($stats['summary']['books_read'] !== 2) {
                return false;
            }

            if ($stats['summary']['average_rating'] !== 4.0) {
                return false;
            }

            // 評価ごとの件数
            if (
                $stats['rating_distribution']->all() !== [
                    1 => 0,
                    2 => 0,
                    3 => 1,
                    4 => 1,
                    5 => 1,
                ]
            ) {
                return false;
            }

            // 高評価書籍TOP5
            $topBooks = $stats['top_rated_books'];

            if (
                $topBooks->pluck('id')->all() !== [
                    $bookA->id,
                    $bookB->id,
                ]
            ) {
                return false;
            }

            // ジャンル別評価
            $genreRatings = $stats['genre_ratings'];

            return $genreRatings->count() === 2;
        });
    }

    /**
     * ログインしていないユーザーはレポート画面を見られない
     */
    public function test_guest_cannot_view_reading_report(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));
    }
}
