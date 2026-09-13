<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 全レビューと全ユーザーを取得
        $reviews = Review::all();
        $users = User::all();

        // 各レビューに割り当てる「いいね件数」のパターン（0〜3件）
        $likeCountsPattern = [2, 3, 1, 0, 2, 3, 1, 2, 0, 3];

        foreach ($reviews as $index => $review) {
            // 付与する「いいね」の件数（0〜3件）を決定
            $count = $likeCountsPattern[$index % count($likeCountsPattern)];

            if ($count === 0) {
                continue; // 0件の場合はスキップ
            }

            // ★重要：レビュー投稿者本人のIDを除外したユーザーリストを作成
            $eligibleUsers = $users->where('id', '!=', $review->user_id);

            // 該当ユーザーから必要な人数分（0〜3人）のIDを順番に取得
            $likeUserIds = $eligibleUsers
                ->skip($index % $eligibleUsers->count())
                ->concat($eligibleUsers) // ループ用に結合
                ->unique('id')
                ->take($count)
                ->pluck('id');

            // syncWithoutDetaching で既存のいいねを維持しつつ紐付け
            $review->likedByUsers()->syncWithoutDetaching($likeUserIds);
        }
    }
}