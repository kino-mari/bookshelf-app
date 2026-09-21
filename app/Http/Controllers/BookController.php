<?php

namespace App\Http\Controllers;


use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\Request;

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


}
