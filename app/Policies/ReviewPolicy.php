<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * ユーザーがレビューを更新できるか確認する
     */
    public function update(User $user, Review $review): bool
    {
        return (int) $user->id === (int) $review->user_id;
    }

    /**
     * ユーザーがレビューを削除できるか確認する
     */
    public function delete(User $user, Review $review): bool
    {
        return (int) $user->id === (int) $review->user_id;
    }
}
