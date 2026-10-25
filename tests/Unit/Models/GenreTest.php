<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Bookモデルとの belongsToMany リレーション（多対多）のテスト
     */
    public function test_genre_belongs_to_many_books(): void
    {
        // 1. 準備 (Arrange)
        $genre = Genre::factory()->create();
        $books = Book::factory()->count(2)->create();

        // 中間テーブル（book_genre）にリレーションを紐づけ
        $genre->books()->attach($books->pluck('id'));

        // 2. 実行 (Act)
        $genre->load('books');

        // 3. 検証 (Assert)
        $this->assertCount(2, $genre->books);
        $this->assertTrue($genre->books->pluck('id')->contains($books->first()->id));
    }
}
