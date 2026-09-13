<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 全ユーザーと全書籍を取得
        $users = User::all();
        $books = Book::all();

        // ユーザーごとの登録件数パターン（3〜5冊）
        // 5人のユーザーにそれぞれ 5, 4, 3, 4, 5 冊を配分
        $favoriteCounts = [5, 4, 3, 4, 5];

        foreach ($users as $index => $user) {
            // そのユーザーに割り当てる冊数（デフォルト 3冊）
            $count = $favoriteCounts[$index % count($favoriteCounts)] ?? 3;

            // 各ユーザーごとに少しずつずらして書籍を取得（バラつきを持たせる）
            $favoriteBookIds = $books
                ->skip($index * 2)
                ->concat($books) // 件数が足りない場合用にループさせる
                ->unique('id')
                ->take($count)
                ->pluck('id');

            // syncWithoutDetaching で既存のお気に入りを保持したまま紐付け
            $user->favoriteBooks()->syncWithoutDetaching($favoriteBookIds);
        }
    }
}
