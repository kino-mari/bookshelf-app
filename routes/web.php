<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\RankingController;




//トップページ（一覧画面）を表示するルーティング
Route::get('/', [BookController::class, 'index'])->name('books.index');
Route::get('/books', [BookController::class, 'index']);

// 認証（ログイン済み）必須のルートグループ
Route::middleware('auth')->group(function () {
    //書籍登録画面
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    //書籍登録
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
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

});

//未認証（ゲスト）でもアクセス可能なルート
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
//ランキング
Route::get('/ranking', [RankingController::class, 'index'])
    ->name('ranking.index');