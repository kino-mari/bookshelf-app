<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Userモデルとの belongsTo リレーション（投稿者）のテスト
     */
    public function test_review_belongs_to_user(): void
    {
        // 1. 準備 (Arrange)
        $user = User::factory()->create();
        $review = Review::factory()->for($user)->create();

        // 2. 実行 (Act)
        $relatedUser = $review->user;

        // 3. 検証 (Assert)
        $this->assertTrue($relatedUser->is($user));
    }

    /**
     * Bookモデルとの belongsTo リレーション（対象の本）のテスト
     */
    public function test_review_belongs_to_book(): void
    {
        // 1. 準備 (Arrange)
        $book = Book::factory()->create();
        $review = Review::factory()->for($book)->create();

        // 2. 実行 (Act)
        $relatedBook = $review->book;

        // 3. 検証 (Assert)
        $this->assertTrue($relatedBook->is($book));
    }

    /**
     * Userモデルとの likedByUsers リレーション（いいねしたユーザー多対多）のテスト
     */
    public function test_review_belongs_to_many_liked_by_users(): void
    {
        // 1. 準備 (Arrange)
        $review = Review::factory()->create();
        $users = User::factory()->count(2)->create();

        // 中間テーブル（review_likes）に紐づけ
        $review->likedByUsers()->attach($users->pluck('id'));

        // 2. 実行 (Act)
        $review->load('likedByUsers');

        // 3. 検証 (Assert)
        $this->assertCount(2, $review->likedByUsers);
        $this->assertTrue($review->likedByUsers->pluck('id')->contains($users->first()->id));
    }
}
