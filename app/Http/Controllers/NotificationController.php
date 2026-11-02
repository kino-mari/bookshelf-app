<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    /**
     * 通知一覧を表示する
     */
    public function index(): View
    {
        // ログインユーザーの通知を取得
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(10);

        return view('notifications.index', compact('notifications'));
    }
    /**
     * 通知を既読にする
     */
    public function read(string $id): RedirectResponse
    {
        // ログインユーザーが所有する通知だけを取得
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        // 通知を既読にする
        $notification->markAsRead();

        return redirect()->route('notifications.index')
            ->with('success', '通知を既読にしました。');
    }
}