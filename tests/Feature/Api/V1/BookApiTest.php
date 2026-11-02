<?php

namespace Tests\Feature\Api\V1;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * AP01: 書籍一覧がちゃんと取れるかテスト
     */
    public function test_can_get_book_list()
    {
        // 1. 準備：テスト用の本をファクトリーを使って3冊作っておく
        Book::factory()->count(3)->create();

        // 2. 実行：一覧APIにGETリクエストを送る
        $response = $this->getJson('/api/v1/books');

        // 3. 確認：ステータスが200で、データが3冊ちゃんと返ってきたかチェック！
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** AP02 */
    public function test_can_get_book_detail(): void
    {
        // 書籍を作成
        $book = Book::factory()->create([
            'title' => 'Laravel API入門',
            'author' => 'テスト太郎',
        ]);

        // 書籍詳細APIにアクセス
        $response = $this->getJson('/api/v1/books/'.$book->id);

        // 正常に取得できることを確認
        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Laravel API入門')
            ->assertJsonPath('data.author', 'テスト太郎');
    }

    /** AP02：存在しない書籍の場合 */
    public function test_returns_404_when_book_does_not_exist(): void
    {
        // 存在しないIDで詳細APIにアクセス
        $response = $this->getJson('/api/v1/books/99999');

        // 404エラーになることを確認
        $response->assertStatus(404)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath(
                'message',
                '指定された書籍が見つかりませんでした'
            );
    }

    /**
     * AP03: 新しい本がちゃんと登録できるかテスト
     */
    public function test_can_create_book()
    {
        // 1. 準備：紐付けるためのジャンルをあらかじめ1つ作っておく
        $user = User::factory()->create();
        $this->actingAs($user);

        $genre = Genre::factory()->create();

        // 登録したいデータの準備
        $bookData = [
            'title' => 'テスト用のすごい本',
            'author' => 'テスト太郎',
            'isbn' => '1234567890123', // 13桁の数字
            'published_date' => '2026-10-19',
            'genres' => [$genre->id],
        ];

        // 2. 実行：登録APIにPOSTリクエストでデータを送る
        $response = $this->postJson('/api/v1/books', $bookData);

        // 3. 確認：登録成功のステータス(201)と、successメッセージが返ったかチェック！
        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.title', 'テスト用のすごい本');

        // おまけ：データベースにちゃんと保存されているかも直接確認！
        $this->assertDatabaseHas('books', [
            'title' => 'テスト用のすごい本',
            'isbn' => '1234567890123',
            'user_id' => $user->id,
        ]);
    }

    /**
     * AP04: トークンなしでは書籍を登録できないこと
     */
    public function test_cannot_create_book_without_token(): void
    {
        // トークンを付けずに書籍登録APIへアクセス
        $response = $this->postJson('/api/v1/books', []);

        // 401（認証されていない）が返ることを確認
        $response->assertStatus(401);
    }

    /**
     * AP05: トークンがあれば書籍を登録できること
     */
    public function test_can_create_book_with_token(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // SanctumのAPIトークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 登録する書籍情報
        $bookData = [
            'title' => 'Sanctumテストの本',
            'author' => 'テスト太郎',
            'isbn' => '1234567890123',
            'published_date' => '2026-10-19',
            'genres' => [$genre->id],
        ];

        // トークンを付けて書籍登録APIにアクセス
        $response = $this->withToken($token)
            ->postJson('/api/v1/books', $bookData);

        // 登録成功（201）を確認
        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.title', 'Sanctumテストの本');

        // データベースへの保存を確認
        $this->assertDatabaseHas('books', [
            'title' => 'Sanctumテストの本',
            'user_id' => $user->id,
        ]);
    }

    /**
     * AP06: 他人の書籍を更新できないこと
     */
    public function test_cannot_update_another_users_book(): void
    {
        // 書籍の所有者
        $owner = User::factory()->create();

        // 別のユーザー
        $otherUser = User::factory()->create();

        // 所有者の書籍を作成
        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        // ジャンルを作成
        $genre = Genre::factory()->create();

        // 別のユーザーのトークンを作成
        $token = $otherUser->createToken('test-token')->plainTextToken;

        // 別のユーザーが書籍を更新しようとする
        $response = $this->withToken($token)
            ->putJson('/api/v1/books/'.$book->id, [
                'title' => '不正に変更したタイトル',
                'author' => $book->author,
                'isbn' => $book->isbn,
                'published_date' => $book->published_date->format('Y-m-d'),
                'genres' => [$genre->id],
            ]);

        // 更新できないことを確認
        $response->assertStatus(403);

        // データベースのタイトルが変更されていないことを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => $book->title,
            'user_id' => $owner->id,
        ]);
    }

    /** AP07 */
    public function test_cannot_delete_another_users_book(): void
    {
        // 書籍の所有者
        $owner = User::factory()->create();

        // 別のユーザー
        $otherUser = User::factory()->create();

        // 所有者の書籍を作成
        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        // 別のユーザーのトークンを作成
        $token = $otherUser->createToken('test-token')->plainTextToken;

        // 別のユーザーが書籍を削除しようとする
        $response = $this->withToken($token)
            ->deleteJson('/api/v1/books/'.$book->id);

        // 削除できないことを確認
        $response->assertStatus(403);

        // データベースに書籍が残っていることを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'user_id' => $owner->id,
        ]);
    }

    /** AP08 */
    public function test_can_update_own_book_with_token(): void
    {
        // ログインするユーザー
        $user = User::factory()->create();

        // 自分の書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // ジャンルを作成して書籍に紐付ける
        $genre = Genre::factory()->create();
        $book->genres()->attach($genre->id);

        // トークンを作成
        $token = $user->createToken('test-token')->plainTextToken;

        // 自分の書籍を更新
        $response = $this->withToken($token)
            ->putJson('/api/v1/books/'.$book->id, [
                'title' => '更新後のタイトル',
                'author' => $book->author,
                'isbn' => $book->isbn,
                'published_date' => $book->published_date->format('Y-m-d'),
                'genres' => [$genre->id],
            ]);

        // 更新成功を確認
        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.title', '更新後のタイトル');

        // データベースも更新されていることを確認
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後のタイトル',
            'user_id' => $user->id,
        ]);
    }

    /** AP09 */
    public function test_can_delete_own_book_with_token(): void
    {
        // ログインするユーザー
        $user = User::factory()->create();

        // 自分の書籍を作成
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // トークンを作成
        $token = $user->createToken('test-token')->plainTextToken;

        // 自分の書籍を削除
        $response = $this->withToken($token)
            ->deleteJson('/api/v1/books/'.$book->id);

        // 削除成功を確認
        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // データベースから削除されていることを確認
        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }
}
