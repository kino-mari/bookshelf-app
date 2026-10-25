<?php

namespace Tests\Feature\Api\V1;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
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
}