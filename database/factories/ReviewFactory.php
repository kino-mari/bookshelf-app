<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // リレーション: 自動でUserおよびBookを作成してIDを割り当てる
            'user_id' => User::factory(),
            'book_id' => Book::factory(),

            // カラムの型に合わせたダミーデータ (Faker)
            'rating' => fake()->numberBetween(1, 5),    // 1〜5の評価
            'comment' => fake()->realText(200),         // レビューコメント
        ];
    }
}