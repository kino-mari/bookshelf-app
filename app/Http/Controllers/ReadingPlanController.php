<?php

namespace App\Http\Controllers;

use App\Models\ReadingPlan;
use App\Http\Requests\ReadingPlanRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\Book;


class ReadingPlanController extends Controller
{
    /**
     * 読書計画一覧画面を表示する
     */
    public function index(Request $request): View
    {
        $query = ReadingPlan::with('book')
            ->where('user_id', Auth::id());

        // 状態による絞り込み
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 新しい順に10件ずつ表示
        $plans = $query->latest()->paginate(10);

        // ページ移動後も検索条件を維持
        $plans->appends($request->query());

        return view('reading-plans.index', compact('plans'));
    }

    /**
     * 新規読書計画作成画面を表示する
     */
    public function create(): View
    {
        $books = Book::all();
        return view('reading-plans.create', compact('books'));
    }

    /**
     * 新規読書計画を保存する
     */
    public function store(ReadingPlanRequest $request): RedirectResponse
    {
        ReadingPlan::create([
            'user_id' => Auth::id(),
            'book_id' => $request->book_id,
            'due_date' => $request->due_date,
            'status' => 'unread',
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を作成しました。');
    }

    /**
     * 読書計画を更新する
     */
    public function update(ReadingPlanRequest $request, ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('update', $plan);

        $plan->update([
            'book_id' => $request->book_id,
            'due_date' => $request->due_date,
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を更新しました。');
    }

    /**
     *
     * 読書計画編集画面を表示する
     */
    public function edit(ReadingPlan $plan): View
    {
        $this->authorize('update', $plan);

        return view('reading-plans.edit', compact('plan'));
    }

    /**
     * 読書計画を読了状態にする
     */
    public function complete(ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('update', $plan);

        $plan->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を読了にしました。');
    }

    /**
     * 読書計画を削除する
     */
    public function destroy(ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('delete', $plan);

        $plan->delete();

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を削除しました。');
    }
}