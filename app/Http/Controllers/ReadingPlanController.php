<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReadingPlanController extends Controller
{
    /**
     * 読書計画一覧画面を表示する
     */
    public function index(Request $request): View
    {
        // ログインユーザーの読書計画を取得する
        $query = ReadingPlan::with('book')
            ->where('user_id', Auth::id());

        // 状態による絞り込み
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ページネーション
        $readingPlans = $query->latest()->paginate(10);

        // 絞り込み条件をページ移動後も維持する
        $readingPlans->appends($request->query());

        // Bladeに渡す現在の絞り込み状態
        $currentStatus = $request->filled('status')
            ? (int) $request->status
            : null;

        return view('reading-plans.index', compact(
            'readingPlans',
            'currentStatus'
        ));
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
        // バリデーション済みのデータを取得
        $validated = $request->validated();

        ReadingPlan::create([
            'user_id' => Auth::id(),
            'book_id' => $validated['book_id'],
            'due_date' => $validated['due_date'],
            'status' => 0,
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

        $validated = $request->validated();

        $plan->update([
            'due_date' => $validated['due_date'],
        ]);

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を更新しました。');
    }

    /**
     * 読書計画編集画面を表示する
     */
    public function edit(ReadingPlan $plan): View
    {
        $this->authorize('update', $plan);

        $readingPlan = $plan;

        return view('reading-plans.edit', compact('readingPlan'));
    }

    /**
     * 読書計画を読了状態にする
     */
    public function complete(ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('update', $plan);

        $plan->update([
            'status' => 2,
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
