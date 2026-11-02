<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * 書籍一覧を表示する（検索・ジャンル絞り込み・並び替え・ページネーション対応）
     */
    public function index(Request $request): View
    {
        $query = Book::with(['genres'])
            ->withAvg('reviews', 'rating');

        // タイトル・著者で検索
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;

            $query->where(function ($q) use ($keyword): void {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        // ジャンルで絞り込み
        if ($request->filled('genre')) {
            $query->whereHas('genres', function ($q) use ($request): void {
                $q->where('genres.id', $request->genre);
            });
        }

        // 並び替え
        $sort = $request->input('sort', 'latest');

        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;

            case 'title':
                $query->orderBy('title', 'asc');
                break;

            case 'rating':
                // レビューがない書籍は最後に表示
                $query->orderByRaw(
                    'reviews_avg_rating IS NULL, reviews_avg_rating DESC'
                );
                break;

            case 'latest':
            default:
                $query->latest();
                break;
        }

        // 1ページ10件で表示し、検索条件を引き継ぐ
        $books = $query->paginate(10)->appends($request->query());

        // ジャンル一覧を取得
        $genres = Genre::all();

        return view('books.index', compact('books', 'genres'));
    }

    /**
     * 書籍登録画面を表示する
     */
    public function create(): View
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    /**
     * 書籍詳細を表示する
     */
    public function show(Book $book): View
    {
        // 関連データをまとめて取得する（N+1問題の防止）
        $book->load([
            'genres',
            'favoritedByUsers',
            'reviews.user',
            'reviews.likedByUsers',
        ]);

        return view('books.show', compact('book'));
    }

    /**
     * 書籍編集画面を表示する（所有者のみ）
     */
    public function edit(Book $book): View
    {
        $this->authorize('update', $book);

        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * 書籍情報を更新する（所有者のみ）
     */
    public function update(BookRequest $request, Book $book): RedirectResponse
    {
        // 所有者本人か確認
        $this->authorize('update', $book);

        // バリデーション済みデータを取得
        $validated = $request->validated();

        // ジャンルIDを取り出す
        $genreIds = $validated['genres'] ?? [];

        // ジャンル情報を取り除く
        unset($validated['genres']);

        // フォームの published_date をDBの published_at に変換
        $validated['published_at'] = $validated['published_date'] ?? null;
        unset($validated['published_date']);

        // 書籍情報とジャンルの紐付けをまとめて更新
        DB::transaction(function () use ($book, $validated, $genreIds): void {
            $book->update($validated);

            $book->genres()->sync($genreIds);
        });

        return redirect()
            ->route('books.show', $book)
            ->with('success', '書籍情報を更新しました。');
    }

    /**
     * 書籍を削除する（所有者のみ）
     */
    public function destroy(Book $book): RedirectResponse
    {
        // 所有者本人か確認
        $this->authorize('delete', $book);

        // 書籍を削除
        $book->delete();

        return redirect()
            ->route('books.index')
            ->with('success', '書籍を削除しました。');
    }

    /**
     * 新しい書籍を登録する
     */
    public function store(BookRequest $request): RedirectResponse
    {
        // バリデーション済みデータを取得
        $validated = $request->validated();

        // ジャンルIDを取り出す
        $genreIds = $validated['genres'] ?? [];

        // ジャンル情報を取り除く
        unset($validated['genres']);

        // フォームの published_date をDBの published_at に変換
        $validated['published_at'] = $validated['published_date'] ?? null;
        unset($validated['published_date']);

        // ログインユーザーのIDを追加
        $validated['user_id'] = Auth::id();

        // 書籍登録とジャンルの紐付けをまとめて実行
        $book = DB::transaction(function () use ($validated, $genreIds): Book {
            // 新しい書籍を登録
            $book = Book::create($validated);

            // ジャンルを紐付け
            $book->genres()->sync($genreIds);

            return $book;
        });

        return redirect()
            ->route('books.index')
            ->with('success', '書籍を登録しました。');
    }

    /**
     * ISBNを使ってGoogle Books APIから書籍情報を取得する
     */
    public function searchByIsbn(string $isbn): JsonResponse
    {
        // ISBNが13桁の数字か確認
        if (! preg_match('/^[0-9]{13}$/', $isbn)) {
            return response()->json([
                'message' => 'ISBNは13桁の数字で入力してください。',
            ], 422);
        }

        // Google Books APIに問い合わせる
        $response = Http::get(
            'https://www.googleapis.com/books/v1/volumes',
            [
                'q' => 'isbn:'.$isbn,
                'maxResults' => 10,
            ]
        );

        // API通信に失敗した場合
        if (! $response->successful()) {
            return response()->json([
                'message' => '書籍情報の取得に失敗しました。',
            ], 502);
        }

        // APIから検索結果を取得
        $items = $response->json('items', []);

        // ISBNが完全一致する書籍を探す
        $item = collect($items)->first(function ($item) use ($isbn): bool {
            return collect(
                data_get($item, 'volumeInfo.industryIdentifiers', [])
            )->contains(function ($identifier) use ($isbn): bool {
                return ($identifier['type'] ?? '') === 'ISBN_13'
                    && ($identifier['identifier'] ?? '') === $isbn;
            });
        });

        // 該当する書籍がない場合
        if (! $item) {
            return response()->json([
                'message' => '該当する書籍が見つかりませんでした。',
            ], 404);
        }

        // 書籍情報を取り出す
        $info = $item['volumeInfo'];

        // 出版日をYYYY-MM-DD形式に変換
        $publishedDate = $info['publishedDate'] ?? '';

        if (preg_match('/^\d{4}$/', $publishedDate)) {
            $publishedDate .= '-01-01';
        } elseif (preg_match('/^\d{4}-\d{2}$/', $publishedDate)) {
            $publishedDate .= '-01';
        } elseif (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $publishedDate)) {
            $publishedDate = '';
        }

        // JSON形式で書籍情報を返す
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
