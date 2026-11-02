<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReadingPlanController;
use App\Http\Controllers\NotificationController;


//トップページ（一覧画面）を表示するルーティング
Route::get('/', [BookController::class, 'index'])->name('books.index');
Route::get('/books', [BookController::class, 'index']);

// 認証（ログイン済み）必須のルートグループ
Route::middleware('auth')->group(function () {
    //書籍登録画面
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    //書籍登録
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/isbn/{isbn}', [BookController::class, 'searchByIsbn'])
        ->where('isbn', '[0-9]{13}')
        ->name('books.isbn');
    //お気に入りトグル
    Route::post('/books/{book}/favorites', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    //評価
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // 書籍の編集・削除
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
    // 更新処理
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');

    //いいねトグル
    Route::post('/reviews/{review}/like', [ReviewController::class, 'toggle'])
        ->name('reviews.like');

    // レビューの編集・削除
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    //更新処理
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');

    //お気に入り一覧画面
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');

    //ジャンル
    Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');
    Route::get('genres/create', [GenreController::class, 'create'])->name('genres.create');
    Route::get('genres/{genre}', [GenreController::class, 'show'])->name('genres.show');
    Route::post('/genres', [GenreController::class, 'store'])->name('genres.store');
    Route::get('genres/{genre}/edit', [GenreController::class, 'edit'])->name('genres.edit');
    // ジャンル更新処理（※追加）
    Route::put('/genres/{genre}', [GenreController::class, 'update'])->name('genres.update');
    // ジャンル削除処理（※要件にあったDELETEを追加）
    Route::delete('/genres/{genre}', [GenreController::class, 'destroy'])->name('genres.destroy');

    //マイ読書レポート
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // 読書計画一覧（絞り込み対応）
    Route::get('/reading-plans', [ReadingPlanController::class, 'index'])->name('reading-plans.index');
    // 新規計画作成画面・処理
    Route::get('/reading-plans/create', [ReadingPlanController::class, 'create'])->name('reading-plans.create');
    Route::post('/reading-plans', [ReadingPlanController::class, 'store'])->name('reading-plans.store');
    // 編集画面
    Route::get('/reading-plans/{plan}/edit', [ReadingPlanController::class, 'edit'])->name('reading-plans.edit');
    // 「読了する」ボタン（POST）
    Route::post('/reading-plans/{plan}/complete', [ReadingPlanController::class, 'complete'])->name('reading-plans.complete');
    // 削除処理
    Route::delete('/reading-plans/{plan}', [ReadingPlanController::class, 'destroy'])->name('reading-plans.destroy');
    // 通知一覧
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');
    // 通知を既読にする
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])
        ->name('notifications.read');
});


//未認証（ゲスト）でもアクセス可能なルート
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
//ランキング
Route::get('/ranking', [RankingController::class, 'index'])
    ->name('ranking.index');