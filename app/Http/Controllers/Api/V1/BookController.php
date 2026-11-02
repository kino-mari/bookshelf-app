<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BookIndexRequest;
use App\Http\Requests\Api\V1\BookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    /**
     * 書籍一覧を取得する
     * キーワード検索、ジャンル絞り込み、ページネーションに対応
     */
    public function index(BookIndexRequest $request): AnonymousResourceCollection
    {
        // バリデーション済みの値を取得
        $validated = $request->validated();

        // 書籍の検索クエリを作成
        $query = Book::query();

        // ジャンル、レビュー平均、レビュー件数をまとめて取得
        $query->with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        // キーワード検索
        if (! empty($validated['keyword'])) {
            $keyword = $validated['keyword'];

            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        // ジャンルで絞り込み
        if (! empty($validated['genre_id'])) {
            $genreId = $validated['genre_id'];

            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        // 1ページあたりの表示件数（指定がなければ10件）
        $perPage = $validated['per_page'] ?? 10;

        // ページネーション
        $books = $query->paginate($perPage);

        return BookResource::collection($books);
    }

    /**
     * 指定した書籍の詳細を取得する
     */
    public function show(string $id): BookResource|JsonResponse
    {
        try {
            $book = Book::with(['genres', 'reviews.user'])
                ->findOrFail($id);

            return new BookResource($book);
        } catch (ModelNotFoundException) {
            return response()->json([
                'status' => 'error',
                'message' => '指定された書籍が見つかりませんでした',
            ], 404);
        }
    }

    /**
     * 書籍を新規登録する
     */
    public function store(BookRequest $request): JsonResponse
    {
        // バリデーション済みの値を取得
        $validated = $request->validated();

        // ジャンルIDを取り出す
        $genreIds = $validated['genres'];

        // DBのカラム名に合わせて変換
        $data = $validated;
        $data['user_id'] = $request->user()->id;
        $data['published_at'] = $data['published_date'];

        // DBに存在しない項目を取り除く
        unset($data['published_date'], $data['genres']);

        // 書籍登録とジャンル登録をまとめて実行
        $book = DB::transaction(function () use ($data, $genreIds) {
            $book = Book::create($data);

            $book->genres()->sync($genreIds);

            return $book;
        });

        // ジャンル情報を取得
        $book->load('genres');

        return response()->json([
            'status' => 'success',
            'message' => '書籍を登録しました',
            'data' => new BookResource($book),
        ], 201);
    }

    /**
     * 指定した書籍を更新する
     */
    public function update(BookRequest $request, string $id): JsonResponse
    {
        try {
            // 更新対象の書籍を取得
            $book = Book::findOrFail($id);

            // 書籍の所有者かどうかを確認
            $this->authorize('update', $book);

            // バリデーション済みの値を取得
            $validated = $request->validated();

            // ジャンルIDを取り出す
            $genreIds = $validated['genres'];

            // DBのカラム名に合わせて変換
            $data = $validated;
            $data['published_at'] = $data['published_date'];

            // DBに存在しない項目を取り除く
            unset($data['published_date'], $data['genres']);

            // 書籍情報とジャンル情報をまとめて更新
            DB::transaction(function () use ($book, $data, $genreIds) {
                $book->update($data);

                $book->genres()->sync($genreIds);
            });

            // 更新後のジャンル情報を取得
            $book->load('genres');

            return response()->json([
                'status' => 'success',
                'message' => '書籍を更新しました',
                'data' => new BookResource($book),
            ], 200);
        } catch (ModelNotFoundException) {
            return response()->json([
                'status' => 'error',
                'message' => '指定された書籍が見つかりませんでした',
            ], 404);
        }
    }

    /**
     * 指定した書籍を削除する
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            // 削除対象の書籍を取得
            $book = Book::findOrFail($id);

            // 書籍の所有者かどうかを確認
            $this->authorize('delete', $book);

            // 関連データと書籍をまとめて削除
            DB::transaction(function () use ($book) {
                // ジャンルとの関連を解除
                $book->genres()->detach();

                // お気に入りとの関連を解除
                $book->favoritedByUsers()->detach();

                // レビューを削除
                $book->reviews()->delete();

                // 書籍を削除
                $book->delete();
            });

            return response()->json([
                'status' => 'success',
                'message' => '書籍を削除しました',
            ], 200);
        } catch (ModelNotFoundException) {
            return response()->json([
                'status' => 'error',
                'message' => '指定された書籍が見つかりませんでした',
            ], 404);
        }
    }
}
