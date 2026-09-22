<?php

namespace App\Http\Controllers;


use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Http\Requests\BookRequest;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::with(['genres'])
            ->withAvg('reviews', 'rating')
            ->latest()
            ->paginate(10);

        return view('books.index', compact('books'));
    }

    public function create()
    {
        // フォームのチェックボックス用に全ジャンルを取得
        $genres = Genre::all();
        return view('books.create', compact('genres'));
    }

    public function show(Book $book)
    {
        $book->load([
            'genres',                 // Book -> Genre
            'favoritedByUsers',       // Book -> User (本をお気に入りした人)
            'reviews.user',           // Book -> Review -> User (レビューを書いた人)
            'reviews.likedByUsers',   // Book -> Review -> User (レビューに「いいね」した人)
        ]);
        return view('books.show', compact('book'));
    }

    public function edit(Book $book): View
    {
        // 作成者本人か確認（Policyのupdateメソッドを実行）
        $this->authorize('update', $book);
        // 編集フォームのチェックボックス用に全ジャンルを取得
        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    public function update(BookRequest $request, Book $book): RedirectResponse
    {
        // 1. 作成者本人か確認（Policyのupdateメソッドを実行）
        $this->authorize('update', $book);

        // 2. バリデーション済みデータで書籍情報を更新
        $book->update($request->validated());

        // 3. ジャンルの中間テーブル紐付けを同期（syncで差分を自動更新）
        $book->genres()->sync($request->input('genres'));

        // 4. 詳細画面へリダイレクト
        return redirect()
            ->route('books.show', $book)
            ->with('success', '書籍情報を更新しました。');
    }
    /**
     * DELETE /books/{book}（書籍削除処理）
     */
    public function destroy(Book $book): RedirectResponse
    {
        // 作成者本人か確認（Policyのdeleteメソッドを実行）
        $this->authorize('delete', $book);

        // 書籍の削除（関連するレビュー・お気に入り・ジャンルも自動処理される）
        $book->delete();

        return redirect()->route('books.index')->with('status', '書籍を削除しました。');
    }

    public function store(BookRequest $request)
    {
        // 1. バリデーション済みデータを取得
        $validated = $request->validated();

        // 2. ログインユーザーのIDを追加
        $validated['user_id'] = Auth::id();

        // 3. 書籍を登録（$fillableに定義されたカラムのみ自動で保存されます）
        $book = Book::create($validated);

        // 4. ジャンルの中間テーブル紐付け（book_genreに保存）
        $book->genres()->attach($request->input('genres'));

        // 5. リダイレクト
        return redirect()->route('books.index')->with('success', '書籍を登録しました。');
    }
}
