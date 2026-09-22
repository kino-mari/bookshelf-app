<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index()
    {
        return view('favorites.index');
    }
    public function toggle(Book $book): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // toggleメソッドでON/OFFを切り替える
        $user->favoriteBooks()->toggle($book->id);

        return back();
    }
}
