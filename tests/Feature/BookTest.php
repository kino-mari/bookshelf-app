<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;

class BookTest extends TestCase
{
    use RefreshDatabase;
    /**
     * ゲスト・認証ユーザーともに GET / または GET /books でアクセスでき、
     * 書籍が10件ずつページネーション表示され、ジャンルが表示されること。
     */
    public function test_guest_and_authenticated_user_can_view_books_index(): void
    {
        $genre = Genre::factory()->create(['name' => '技術書']);

        $books = Book::factory()->count(15)->create();
        foreach ($books as $book) {
            $book->genres()->attach($genre);
        }

        $guestResponse = $this->get('/');
        $guestResponse->assertOk();
        $guestResponse->assertSee('技術書');
        $guestResponse->assertViewHas('books', function ($viewBooks) {
            return $viewBooks->count() === 10;
        });

        $user = User::factory()->create();
        $authResponse = $this->actingAs($user)->get('/books');
        $authResponse->assertOk();
        $authResponse->assertSee('技術書');
        $authResponse->assertViewHas('books', function ($viewBooks) {
            return $viewBooks->count() === 10;
        });
    }


    /**
     * ゲストでも GET /books/{book} でアクセスでき、詳細が表示されること。
     */
    public function test_guest_can_view_book_detail(): void
    {
        $genre = Genre::factory()->create(['name' => 'Web開発']);
        $book = Book::factory()->create([
            'title' => 'Laravel実践ガイド',
            'author' => '山田太郎',
        ]);
        $book->genres()->attach($genre);

        $this->get("/books/{$book->id}")
            ->assertOk()
            ->assertSee('Laravel実践ガイド')
            ->assertSee('山田太郎')
            ->assertSee('Web開発');
    }

    /**
     * ゲスト制限およびログインユーザーによる書籍とジャンルの登録処理
     */
    public function test_book_create_and_store(): void
    {
        $this->get('/books/create')->assertRedirect('/login');
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post('/books', [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9784123456789',
            'published_date' => '2026-01-01',
            'description' => '基礎から学ぶ解説書。',
            'genres' => [$genre->id],
        ]);

        $this->assertDatabaseHas('books', ['title' => 'Laravel入門']);
        $this->assertDatabaseHas('book_genre', ['genre_id' => $genre->id]);
    }

    /**
     * 作成者本人の更新・削除および作成者以外のアクセス拒否（認可制御）
     */
    public function test_book_update_and_delete_authorization(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create(['user_id' => $owner->id, 'title' => '旧タイトル']);

        // 1. 作成者以外のユーザーが編集・削除リクエストを送った際、アクセスが拒否されること（403が返ること）
        $this->actingAs($other)->get("/books/{$book->id}/edit")->assertForbidden();

        // バリデーションを通るデータを渡しつつ、他人による更新が 403 になることを検証
        $this->actingAs($other)->put("/books/{$book->id}", [
            'title' => '不正改変',
            'author' => 'テスト作者',
            'isbn' => '9784123456789',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ])->assertForbidden();

        $this->actingAs($other)->delete("/books/{$book->id}")->assertForbidden();

        // 2. 作成者本人が PUT /books/{book} で書籍を更新できること
        $this->actingAs($owner)->put("/books/{$book->id}", [
            'title' => '新タイトル',
            'author' => $book->author,
            'isbn' => $book->isbn ?? '9784123456789',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ]);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => '新タイトル']);

        // 3. 作成者本人が DELETE /books/{book} を実行した際、書籍が削除されること
        $this->actingAs($owner)->delete("/books/{$book->id}");
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
}
