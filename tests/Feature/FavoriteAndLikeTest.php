<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Review;

class FavoriteAndLikeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * お気に入り機能（ゲスト制限およびトグル動作）
     */
    public function test_favorite_toggle(): void
    {
        $book = Book::factory()->create();

        // 1. 未認証の状態で実行した際、/login へリダイレクトされること
        $this->post("/books/{$book->id}/favorites")->assertRedirect('/login');

        // 2. 認証状態で実行した際、追加（トグル1回目）されること
        $user = User::factory()->create();

        $this->actingAs($user)->post("/books/{$book->id}/favorites");
        // ※中間テーブル名にあわせて 'favorites' から 'book_user' に修正
        $this->assertDatabaseHas('book_user', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // 3. 登録済みで再度実行した際、解除（トグル2回目）されること
        $this->actingAs($user)->post("/books/{$book->id}/favorites");
        $this->assertDatabaseMissing('book_user', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /**
     * GET /favorites にて、ログインユーザーのお気に入りのみが10件/ページで表示されること。
     */
    public function test_user_can_view_own_favorites_with_pagination(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // 自分の分（15件）と他人の分（1件）のお気に入りを作成
        $ownBooks = Book::factory()->count(15)->create();
        // ※モデル定義に合わせて favoriteBooks() を使用
        $user->favoriteBooks()->attach($ownBooks->pluck('id'));

        $otherBook = Book::factory()->create(['title' => '他人の本']);
        $otherUser->favoriteBooks()->attach($otherBook->id);

        // ログインしてアクセス
        $response = $this->actingAs($user)->get('/favorites');

        $response->assertOk()
            ->assertDontSee('他人の本')
            ->assertViewHas('books', fn($books) => $books->count() === 10);
    }

    /**
     * レビューのいいねトグル機能（ゲスト制限および追加/解除の検証）
     */
    public function test_review_like_toggle(): void
    {
        $book = Book::factory()->create();
        $review = Review::factory()->create(['book_id' => $book->id]);

        // 1. 未認証の状態で実行した際、/login へリダイレクトされること
        $this->post("/reviews/{$review->id}/like")->assertRedirect('/login');

        // 2. 認証状態で実行した際、いいねが追加（トグル1回目）されること
        $user = User::factory()->create();

        $this->actingAs($user)->post("/reviews/{$review->id}/like");

        // DBにいいね（Like）が保存されていることを検証（※テーブル名にあわせて 'likes' から 'review_likes' に修正）
        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        // 3. いいね済みで再度実行した際、解除（トグル2回目）されること
        $this->actingAs($user)->post("/reviews/{$review->id}/like");

        // DBからいいねが削除されていることを検証
        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }
}