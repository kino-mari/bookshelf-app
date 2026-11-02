<?php

namespace Database\Factories;

use App\Models\Genre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Genre>
 */
class GenreFactory extends Factory
{
    /**
     * ジャンルのダミーデータを定義する
     *
     * @return array<string, mixed>
     */
    protected $model = Genre::class;

    public function definition(): array
    {
        return [
            // 重複（unique）エラーを防ぐため unique() を付与
            'name' => fake()->unique()->word(),
        ];
    }
}
