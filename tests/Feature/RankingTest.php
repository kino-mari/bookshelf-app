<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Review;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ランキング画面が表示され、評価の高い書籍が並び、レビューのない本が含まれないこと
     */
    public function test_ranking_displays_top_books_with_reviews(): void
    {
        // 1. レビューがある書籍を作成（例：評価5）
        $bookWithReview = Book::factory()->create(['title' => '高評価の本']);
        Review::factory()->create([
            'book_id' => $bookWithReview->id,
            'rating' => 5,
        ]);

        // 2. レビューがない書籍を作成
        $bookWithoutReview = Book::factory()->create(['title' => 'レビューのない本']);

        // 3. ゲストでランキング画面にアクセス
        $response = $this->get('/ranking');

        $response->assertOk()
            ->assertSee('高評価の本')         // 評価のある本が表示されていること
            ->assertDontSee('レビューのない本'); // レビューのない本は除外されていること
    }
}