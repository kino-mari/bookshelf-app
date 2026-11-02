<?php

namespace App\Http\Controllers;


use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Http\Requests\BookRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $query = Book::with(['genres'])
            ->withAvg('reviews', 'rating');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword): void {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('genre')) {
            $query->whereHas('genres', function ($q) use ($request): void {
                $q->where('genres.id', $request->genre);
            });
        }

        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            case 'rating':
                $query->orderByRaw('reviews_avg_rating IS NULL, reviews_avg_rating DESC');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $books = $query->paginate(10)->appends($request->query());

        $genres = Genre::all();

        return view('books.index', compact('books', 'genres'));
    }

    public function create(): View
    {
        // フォームのチェックボックス用に全ジャンルを取得
        $genres = Genre::all();
        return view('books.create', compact('genres'));
    }

    public function show(Book $book): View
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
        // 1. 作成者本人か確認
        $this->authorize('update', $book);

        // 2. バリデーション済みデータを取得
        $validated = $request->validated();

        // 3. ジャンルIDを取り出す
        $genreIds = $validated['genres'] ?? [];

        // 4. ジャンル情報を取り除く
        unset($validated['genres']);

        $validated['published_at'] = $validated['published_date'] ?? null;
        unset($validated['published_date']);

        // 5. 書籍情報を更新
        $book->update($validated);

        // 6. ジャンルの紐付けを同期
        $book->genres()->sync($genreIds);

        // 7. 詳細画面へ移動
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

        return redirect()->route('books.index')->with('success', '書籍を削除しました。');
    }

    public function store(BookRequest $request): RedirectResponse
    {
        // 1. バリデーション済みデータを取得
        $validated = $request->validated();

        // 2. ジャンルIDを取り出す
        $genreIds = $validated['genres'] ?? [];

        // 3. ジャンル情報を取り除く
        unset($validated['genres']);

        $validated['published_at'] = $validated['published_date'] ?? null;
        unset($validated['published_date']);

        // 4. ログインユーザーのIDを追加
        $validated['user_id'] = Auth::id();

        // 5. 書籍を登録
        $book = Book::create($validated);

        // 6. ジャンルを中間テーブルに紐付け
        $book->genres()->sync($genreIds);

        // 7. 書籍一覧画面へ移動
        return redirect()
            ->route('books.index')
            ->with('success', '書籍を登録しました。');
    }

    public function searchByIsbn(string $isbn): JsonResponse
    {
        // 1. ISBNが13桁の数字か確認
        if (!preg_match('/^[0-9]{13}$/', $isbn)) {
            return response()->json([
                'message' => 'ISBNは13桁の数字で入力してください。',
            ], 422);
        }

        // 2. Google Books APIに問い合わせる
        $response = Http::get(
            'https://www.googleapis.com/books/v1/volumes',
            [
                'q' => 'isbn:' . $isbn,
                'maxResults' => 10,
            ]
        );

        // 3. API通信に失敗した場合
        if (!$response->successful()) {
            return response()->json([
                'message' => '書籍情報の取得に失敗しました。',
            ], 502);
        }

        // 4. APIから検索結果を取得
        $items = $response->json('items', []);

        // 5. ISBNが完全一致する書籍を探す
        $item = collect($items)->first(function ($item) use ($isbn) {
            return collect(
                data_get($item, 'volumeInfo.industryIdentifiers', [])
            )->contains(function ($identifier) use ($isbn) {
                return ($identifier['type'] ?? '') === 'ISBN_13'
                    && ($identifier['identifier'] ?? '') === $isbn;
            });
        });

        // 6. 該当する書籍がない場合
        if (!$item) {
            return response()->json([
                'message' => '該当する書籍が見つかりませんでした。',
            ], 404);
        }

        // 7. 書籍情報を取り出す
        $info = $item['volumeInfo'];

        // 8. 出版日をYYYY-MM-DD形式に変換
        $publishedDate = $info['publishedDate'] ?? '';

        if (preg_match('/^\d{4}$/', $publishedDate)) {
            $publishedDate .= '-01-01';
        } elseif (preg_match('/^\d{4}-\d{2}$/', $publishedDate)) {
            $publishedDate .= '-01';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $publishedDate)) {
            $publishedDate = '';
        }

        // 9. JSON形式で書籍情報を返す
        return response()->json([
            'title' => $info['title'] ?? '',
            'author' => implode(', ', $info['authors'] ?? []),
            'isbn' => $isbn,
            'published_date' => $publishedDate,
            'description' => strip_tags($info['description'] ?? ''),
            'image_url' => $info['imageLinks']['thumbnail'] ?? '',
        ]);
    }
}