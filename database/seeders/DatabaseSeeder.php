<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 依存関係に基づいた正しい実行順序
        $this->call([
            UserSeeder::class,       // 1. ユーザー作成（全ての親）
            GenreSeeder::class,      // 2. ジャンル作成（書籍の親）
            BookSeeder::class,       // 3. 書籍作成（User, Genre を参照）
            ReviewSeeder::class,     // 4. レビュー作成（User, Book を参照）
            FavoriteSeeder::class,   // 5. お気に入り作成（User, Book を参照）
            ReviewLikeSeeder::class, // 6. レビューいいね作成（User, Review を参照）
        ]);
    }
}