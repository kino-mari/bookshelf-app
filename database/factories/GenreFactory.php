<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Genre;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Genre>
 */
class GenreFactory extends Factory
{
    /**
     * Define the model's default state.
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
