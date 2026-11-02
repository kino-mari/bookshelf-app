<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

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

        $this->actingAs($user)->post('/books', [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9784123456789',
            'published_date' => '2026-01-01',
            'description' => '基礎から学ぶ解説書。',
            'genres' => [$genre->id],
        ]);

        $this->assertDatabaseHas('books', ['title' => 'Laravel入門']);
        $book = Book::where('title', 'Laravel入門')->first();
        $this->assertNotNull($book);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);

        $this->assertDatabaseHas('books', [
            'title' => 'Laravel入門',
            'user_id' => $user->id,
        ]);
    }

    /**
     * 作成者本人の更新・削除および作成者以外のアクセス拒否（認可制御）
     */
    public function test_book_update_and_delete_authorization(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
            'title' => '旧タイトル',
        ]);

        // 1. 作成者以外のユーザーは編集・削除できない
        $this->actingAs($other)
            ->get("/books/{$book->id}/edit")
            ->assertForbidden();

        $this->actingAs($other)->put("/books/{$book->id}", [
            'title' => '不正改変',
            'author' => 'テスト作者',
            'isbn' => '9784123456789',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ])->assertForbidden();

        $this->actingAs($other)
            ->delete("/books/{$book->id}")
            ->assertForbidden();

        // 2. 作成者本人は編集画面を表示できる
        $this->actingAs($owner)
            ->get("/books/{$book->id}/edit")
            ->assertOk();

        // 3. 作成者本人は書籍を更新できる
        $this->actingAs($owner)->put("/books/{$book->id}", [
            'title' => '新タイトル',
            'author' => $book->author,
            'isbn' => $book->isbn ?? '9784123456789',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '新タイトル',
        ]);

        // 4. 作成者本人は書籍を削除できる
        $this->actingAs($owner)
            ->delete("/books/{$book->id}");

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    public function test_can_search_books_by_title(): void
    {
        Book::factory()->create(['title' => 'Laravel入門']);

        Book::factory()->create(['title' => 'PHP入門']);

        $response = $this->get('/books?keyword=Laravel');

        $response->assertSee('Laravel入門');
        $response->assertDontSee('PHP入門');
    }

    public function test_can_search_books_by_author(): void
    {
        Book::factory()->create([
            'title' => 'PHP入門',
            'author' => '山田太郎',
        ]);

        Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '佐藤花子',
        ]);

        $response = $this->get('/books?keyword=山田太郎');

        $response->assertSee('PHP入門');
        $response->assertDontSee('Laravel入門');
    }

    public function test_can_search_books_by_partial_keyword(): void
    {
        Book::factory()->create(['title' => 'Laravel実践ガイド']);
        Book::factory()->create(['title' => 'PHP入門']);

        $response = $this->get('/books?keyword=実践');

        $response->assertSee('Laravel実践ガイド');
        $response->assertDontSee('PHP入門');
    }

    public function test_can_filter_books_by_genre(): void
    {
        $genre = Genre::factory()->create(['name' => '技術書']);
        $novelGenre = Genre::factory()->create(['name' => '小説']);

        $book = Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        $book->genres()->attach($genre); // genres()書籍に紐づくジャンルを取得するリレーション

        $novelBook = Book::factory()->create([
            'title' => '吾輩は猫である',
        ]);

        $novelBook->genres()->attach($novelGenre);

        $response = $this->get('/books?genre='.$genre->id);

        $response->assertSee('Laravel入門');
        $response->assertDontSee('吾輩は猫である');
    }

    public function test_search_returns_no_books_when_keyword_does_not_match(): void
    {
        Book::factory()->create(['title' => '小説']);

        $response = $this->get('/books?keyword=存在しない本');

        $response->assertDontSee('小説');
    }

    public function test_can_sort_books_by_latest(): void
    {
        Book::factory()->create([
            'title' => '古い書籍',
            'created_at' => '2025-01-01 10:00:00',
        ]);

        Book::factory()->create([
            'title' => '新しい書籍',
            'created_at' => '2026-01-01 10:00:00',
        ]);

        $response = $this->get('/books?sort=latest');

        $response->assertSeeInOrder([
            '新しい書籍',
            '古い書籍',
        ]);
    }

    public function test_can_sort_books_by_oldest(): void
    {
        Book::factory()->create([
            'title' => '古い書籍',
            'created_at' => '2025-01-01 10:00:00',
        ]);

        Book::factory()->create([
            'title' => '新しい書籍',
            'created_at' => '2026-01-01 10:00:00',
        ]);

        $response = $this->get('/books?sort=oldest');

        $response->assertSeeInOrder([
            '古い書籍',
            '新しい書籍',
        ]);
    }

    public function test_can_sort_books_by_title(): void
    {
        Book::factory()->create(['title' => 'PHP入門']);
        Book::factory()->create(['title' => 'Laravel入門']);

        $response = $this->get('/books?sort=title');

        $response->assertSeeInOrder([
            'Laravel入門',
            'PHP入門',
        ]);
    }

    public function test_can_sort_books_by_rating(): void
    {
        // ① ユーザーを作成する
        $user = User::factory()->create();

        // ② 書籍を3冊作成する
        $highBook = Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        $lowBook = Book::factory()->create([
            'title' => 'PHP入門',
        ]);

        $noReviewBook = Book::factory()->create([
            'title' => '吾輩は猫である',
        ]);

        // ③ 評価5のレビューを作成する
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $highBook->id,
            'rating' => 5,
        ]);

        // ④ 評価3のレビューを作成する
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $lowBook->id,
            'rating' => 3,
        ]);

        // ⑤ 評価順でアクセスする
        $response = $this->get('/books?sort=rating');

        // ⑥ 評価の高い順、レビューなしは最後になることを確認する
        $response->assertSeeInOrder([
            'Laravel入門',
            'PHP入門',
            '吾輩は猫である',
        ]);
    }

    public function test_guest_cannot_update_or_delete_books(): void
    {
        // ① 書籍を作成する
        $book = Book::factory()->create();

        // ② ゲストが書籍を更新できないことを確認する
        $this->put("/books/{$book->id}", [
            'title' => '変更後のタイトル',
            'author' => '山田太郎',
            'isbn' => '9784123456789',
            'published_date' => '2026-01-01',
        ])->assertRedirect('/login');

        $this->delete("/books/{$book->id}")
            ->assertRedirect('/login');
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }

    /**
     * ISBNが13桁ではない場合、422エラーを返すこと
     */
    public function test_isbn_search_returns_422_when_isbn_is_invalid(): void
    {
        // ログインユーザーを作成
        $user = User::factory()->create();

        // 12桁のISBNで検索する
        $response = $this->actingAs($user)
            ->get('/books/isbn/123456789012');

        // 422エラーが返ることを確認
        $response->assertStatus(422);

        // エラーメッセージを確認
        $response->assertJson([
            'message' => 'ISBNは13桁の数字で入力してください。',
        ]);
    }

    /**
     * Google Books APIとの通信に失敗した場合、502エラーを返すこと
     */
    public function test_isbn_search_returns_502_when_api_request_fails(): void
    {
        // ログインユーザーを作成
        $user = User::factory()->create();

        // Google Books APIが500エラーを返す状況を再現
        Http::fake([
            'www.googleapis.com/*' => Http::response([], 500),
        ]);

        // ISBNで検索
        $response = $this->actingAs($user)
            ->get('/books/isbn/9784123456789');

        // 502エラーが返ることを確認
        $response->assertStatus(502);

        // エラーメッセージを確認
        $response->assertJson([
            'message' => '書籍情報の取得に失敗しました。',
        ]);
    }

    /**
     * 該当するISBNの書籍が見つからない場合、404エラーを返すこと
     */
    public function test_isbn_search_returns_404_when_book_is_not_found(): void
    {
        // ログインユーザーを作成
        $user = User::factory()->create();

        // Google Books APIが別のISBNの書籍を返す状況を再現
        Http::fake([
            'www.googleapis.com/*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'industryIdentifiers' => [
                                [
                                    'type' => 'ISBN_13',
                                    'identifier' => '0000000000000',
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        // ISBNで検索
        $response = $this->actingAs($user)
            ->get('/books/isbn/9784123456789');

        // 404エラーが返ることを確認
        $response->assertStatus(404);

        // エラーメッセージを確認
        $response->assertJson([
            'message' => '該当する書籍が見つかりませんでした。',
        ]);
    }

    /**
     * ISBN検索で書籍が見つかった場合、書籍情報を返すこと
     */
    public function test_isbn_search_returns_book_when_found(): void
    {
        // ログインユーザーを作成
        $user = User::factory()->create();

        // Google Books APIが書籍情報を返す状況を再現
        Http::fake([
            'www.googleapis.com/*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'Laravel実践ガイド',
                            'authors' => ['山田太郎'],
                            'publishedDate' => '2024-05',
                            'description' => '<p>基礎から学ぶ</p>',
                            'imageLinks' => [
                                'thumbnail' => 'https://example.com/book.jpg',
                            ],
                            'industryIdentifiers' => [
                                [
                                    'type' => 'ISBN_13',
                                    'identifier' => '9784123456789',
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        // ISBNで検索
        $response = $this->actingAs($user)
            ->get('/books/isbn/9784123456789');

        // 200エラー（成功）が返ることを確認
        $response->assertStatus(200);

        // 書籍情報が正しく返ることを確認
        $response->assertJson([
            'title' => 'Laravel実践ガイド',
            'author' => '山田太郎',
            'isbn' => '9784123456789',
            'published_date' => '2024-05-01',
            'description' => '基礎から学ぶ',
            'image_url' => 'https://example.com/book.jpg',
        ]);
    }
}
