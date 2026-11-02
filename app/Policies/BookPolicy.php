<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    /**
     * ユーザーが書籍を更新できるか確認する
     */
    public function update(User $user, Book $book): bool
    {
        return (int) $user->id === (int) $book->user_id;
    }

    /**
     * ユーザーが書籍を削除できるか確認する
     */
    public function delete(User $user, Book $book): bool
    {
        return (int) $user->id === (int) $book->user_id;
    }
}
