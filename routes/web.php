<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BookController;

//トップページ（一覧画面）を表示するルーティング
Route::get('/', [BookController::class, 'index'])->name('books.index');
Route::get('/books', [BookController::class, 'index']);

// 認証（ログイン済み）必須のルートグループ
Route::middleware('auth')->group(function () {
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
});

//未認証（ゲスト）でもアクセス可能なルート
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
