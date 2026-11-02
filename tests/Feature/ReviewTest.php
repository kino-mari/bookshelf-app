<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * レビュー投稿（ゲスト制限およびログインユーザーによる投稿保存）
     */
    public function test_review_store(): void
    {
        $book = Book::factory()->create();

        // 1. ゲストが実行した際、/login へリダイレクトされること
        $this->post("/books/{$book->id}/reviews", [
            'rating' => 5,
            'comment' => '素晴らしい本でした！',
        ])->assertRedirect('/login');

        // 2. ログインユーザーが投稿した際、DBに保存されること
        $user = User::factory()->create();

        $this->actingAs($user)->post("/books/{$book->id}/reviews", [
            'rating' => 5,
            'comment' => '素晴らしい本でした！',
        ]);

        // 3.データベースへの保存確認
        $this->assertDatabaseHas('reviews', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 5,
            'comment' => '素晴らしい本でした！',
        ]);
    }

    /**
     * レビューの更新・削除および投稿者以外の認可制御
     */
    public function test_review_update_and_delete_authorization(): void
    {
        $author = User::factory()->create();
        $other = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $author->id, 'rating' => 3, 'comment' => '旧コメント']);

        // 1. 投稿者以外が編集・削除しようとしたときに拒否（403）されること
        $this->actingAs($other)->get("/reviews/{$review->id}/edit")->assertForbidden();
        $this->actingAs($other)->put("/reviews/{$review->id}", ['rating' => 1, 'comment' => '改変'])->assertForbidden();
        $this->actingAs($other)->delete("/reviews/{$review->id}")->assertForbidden();

        // 2. 投稿者本人が PUT /reviews/{review} でレビューを更新できること
        $this->actingAs($author)->put("/reviews/{$review->id}", [
            'rating' => 5,
            'comment' => '新コメント',
        ]);
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'rating' => 5, 'comment' => '新コメント']);

        // 3. 投稿者本人が DELETE /reviews/{review} でレビューを削除できること
        $this->actingAs($author)->delete("/reviews/{$review->id}");
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }
}
