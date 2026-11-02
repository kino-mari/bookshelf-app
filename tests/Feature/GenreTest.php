<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ジャンル一覧および詳細ページが表示されること
     */
    public function test_genres_index_and_detail_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => 'プログラミング']);

        // 1. ジャンル一覧表示のテスト
        $this->actingAs($user)
            ->get('/genres')
            ->assertOk()
            ->assertSee('プログラミング');

        // 2. ジャンル詳細表示のテスト
        $this->actingAs($user)
            ->get("/genres/{$genre->id}")
            ->assertOk()
            ->assertSee('プログラミング');
    }

    /**
     * 認証ユーザーが新しいジャンルを登録できること
     */
    public function test_user_can_store_genre(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/genres', [
            'name' => '小説',
        ]);

        $this->assertDatabaseHas('genres', [
            'name' => '小説',
        ]);
    }

    /**
     * 書籍が紐付いていないジャンルを正常に削除できること
     */
    public function test_user_can_delete_unlinked_genre(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $this->actingAs($user)->delete("/genres/{$genre->id}");

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);
    }

    /**
     * 書籍が紐付いているジャンルの削除が制限されること
     */
    public function test_user_cannot_delete_genre_linked_with_books(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();

        // 書籍とジャンルを紐付ける
        $book->genres()->attach($genre->id);

        // 削除リクエストを送信
        $response = $this->actingAs($user)->delete("/genres/{$genre->id}");

        // 削除されずにDBに残っていること、またはエラー等でリダイレクト/拒否されること
        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
        ]);
    }
}
