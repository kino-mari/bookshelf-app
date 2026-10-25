<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create();
        // $book->user 経由で取得したモデルが $user と同一か検証
        $this->assertTrue($book->user->is($user));
    }

    public function test_book_has_many_reviews(): void
    {
        $book = Book::factory()->create();
        $reviews = Review::factory()->count(2)->for($book)->create();

        $book->load('reviews');

        $this->assertCount(2, $book->reviews);
        $this->assertInstanceOf(Review::class, $book->reviews->first());
        $this->assertTrue($book->reviews->pluck('id')->contains($reviews->first()->id));
    }

    public function test_book_belongs_to_many_genres(): void
    {
        $book = Book::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $book->load('genres');

        $this->assertCount(2, $book->genres);
        $this->assertTrue($book->genres->pluck('id')->contains($genres->first()->id));
    }

    public function test_book_belongs_to_many_favorited_by_users(): void
    {
        $book = Book::factory()->create();
        $users = User::factory()->count(2)->create();

        $book->favoritedByUsers()->attach($users->pluck('id'));

        $book->load('favoritedByUsers');

        $this->assertCount(2, $book->favoritedByUsers);
        $this->assertTrue($book->favoritedByUsers->pluck('id')->contains($users->first()->id));
    }

    public function test_published_date_is_casted_to_date(): void
    {
        $book = Book::factory()->create([
            'published_date' => '2024-01-01',
        ]);

        $this->assertInstanceOf(Carbon::class, $book->published_date);
        $this->assertEquals('2024-01-01', $book->published_date->format('Y-m-d'));
    }
}
