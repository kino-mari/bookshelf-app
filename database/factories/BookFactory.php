<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Book;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{

    protected $model = Book::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // リレーション: 自動でUserを作成してIDを割り当てる
            'user_id' => User::factory(),
            // カラムの型に合わせたダミーデータ (Faker)
            'title' => fake()->realText(20),                      // 書籍タイトル
            'author' => fake()->name(),                          // 著者名
            'isbn' => fake()->unique()->isbn13(),                 // 13桁のユニークなISBN
            'published_date' => fake()->date(),                    // 出版日 (YYYY-MM-DD)
            'description' => fake()->realText(100),               // 概要 (nullableですがテスト用に定義)
            'image_url' => fake()->imageUrl(640, 480, 'books'),  // 画像URL (nullable)
        ];
    }
}
