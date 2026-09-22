<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;


class ReviewController extends Controller
{
    public function store(ReviewRequest $request, Book $book): RedirectResponse
    {
        // FormRequestのバリデーションを通過した値のみ取得
        $validated = $request->validated();

        // ログインユーザーIDと紐づけて保存
        $book->reviews()->create([
            'user_id' => Auth::id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return back()->with('status', 'レビューを投稿しました。');
    }

    public function toggle(Review $review): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // toggleメソッドで「いいね」の追加/解除を判別して切り替える
        $user->likedReviews()->toggle($review->id);

        return back();
    }

    public function edit(Review $review): View
    {
        // 投稿者本人か確認（Policyのupdateメソッドを実行）
        $this->authorize('update', $review);

        return view('reviews.edit', compact('review'));
    }

    /**
     * DELETE /reviews/{review}（レビュー削除処理）
     */
    public function destroy(Review $review): RedirectResponse
    {
        // 投稿者本人か確認（Policyのdeleteメソッドを実行）
        $this->authorize('delete', $review);

        // レビューの削除（関連するいいねも自動処理される）
        $review->delete();

        return back()->with('status', 'レビューを削除しました。');
    }


}
