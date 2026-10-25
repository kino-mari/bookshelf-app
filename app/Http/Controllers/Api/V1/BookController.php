<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BookIndexRequest;
use App\Http\Requests\Api\V1\BookRequest;
use App\Http\Resources\BookDetailResource;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    /**
     * AP01: 書籍一覧API
     * GET /api/v1/books
     */
    public function index(BookIndexRequest $request)
    {
        $validated = $request->validated();

        $query = Book::query();

        // ジャンル情報、平均評価、レビュー件数を取得（N+1問題対策）
        $query->with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        // キーワード検索（タイトルまたは著者名）
        if (!empty($validated['keyword'])) {
            $keyword = $validated['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        // ジャンルでの絞り込み
        if (!empty($validated['genre_id'])) {
            $genreId = $validated['genre_id'];
            $query->whereHas('genres', function ($q) use ($genreId) {
                $q->where('genres.id', $genreId);
            });
        }

        // ページネーション処理
        $perPage = $validated['per_page'] ?? 10;
        $books = $query->paginate($perPage);

        return BookResource::collection($books);
    }

    /**
     * AP02: 書籍詳細API
     * GET /api/v1/books/{id}
     */
    public function show(string $id)
    {
        try {
            // 指定IDの書籍を取得（存在しない場合は 404）
            // ジャンル情報とレビュー（投稿者情報含む）を事前ロード
            $book = Book::with(['genres', 'reviews.user'])->findOrFail($id);

            return new BookDetailResource($book);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => '指定された書籍が見つかりませんでした',
            ], 404);
        }
    }

    /**
     * AP03: 書籍登録API
     * POST /api/v1/books
     */
    public function store(BookRequest $request)
    {
        $validated = $request->validated();

        $book = DB::transaction(function () use ($request, $validated) {
            // ログイン中のユーザーIDを自動で追加して書籍データを登録する
            $data = $request->except('genres');
            $data['user_id'] = auth()->id(); // ← これを追加！

            $book = Book::create($data);

            // 多対多リレーション（ジャンル）の紐付け
            if (isset($validated['genres'])) {
                $book->genres()->sync($validated['genres']);
            }

            return $book;
        });

        $book->load('genres');

        // 登録成功時: 201 Created
        return response()->json([
            'status' => 'success',
            'message' => '書籍を登録しました',
            'data' => new BookResource($book),
        ], 201);
    }

    /**
     * AP04: 書籍更新API
     * PUT /api/v1/books/{id}
     */
    public function update(BookRequest $request, string $id)
    {
        try {
            $book = Book::findOrFail($id);

            $validated = $request->validated();

            DB::transaction(function () use ($book, $validated) {
                // 書籍データの更新
                $book->update($validated);

                // ジャンル紐付けの更新
                if (isset($validated['genres'])) {
                    $book->genres()->sync($validated['genres']);
                }
            });

            $book->load('genres');

            // 更新成功時: 200 OK
            return response()->json([
                'status' => 'success',
                'message' => '書籍情報を更新しました',
                'data' => new BookResource($book),
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => '指定された書籍が見つかりませんでした',
            ], 404);
        }
    }

    /**
     * AP05: 書籍削除API
     * DELETE /api/v1/books/{id}
     */
    public function destroy(string $id)
    {
        try {
            $book = Book::findOrFail($id);

            DB::transaction(function () use ($book) {
                // 関連データの削除処理
                $book->genres()->detach();  // ジャンル紐付け（中間テーブル）の解除
                $book->reviews()->delete(); // 関連するレビューの削除

                // お気に入りリレーションが定義されている場合
                if (method_exists($book, 'favorites')) {
                    $book->favorites()->delete();
                }

                // 書籍本体の削除
                $book->delete();
            });

            // 削除成功時: 200 OK（メッセージ付き）または 204 No Content
            return response()->json([
                'status' => 'success',
                'message' => '書籍を削除しました',
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => '指定された書籍が見つかりませんでした',
            ], 404);
        }
    }
}