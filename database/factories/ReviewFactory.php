<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    /**
     * レビューのダミーデータを定義する
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 紐づくユーザーと書籍を自動作成する
            'user_id' => User::factory(),
            'book_id' => Book::factory(),

            // レビューのダミーデータ
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->realText(200),
        ];
    }
}
