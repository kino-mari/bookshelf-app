<?php

namespace App\Http\Controllers;


use App\Http\Requests\GenreRequest;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class GenreController extends Controller
{
    public function index()
    {
        $genres = Genre::withCount('books')->get();
        return view('genres.index', compact('genres'));
    }

    public function create()
    {
        return view('genres.create');
    }

    public function show(Genre $genre)
    {
        $books = $genre->books()->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    public function store(GenreRequest $request)
    {
        $validated = $request->validated();
        Genre::create($validated);
        return redirect()->route('genres.index')->with('success', 'ジャンルを登録しました。');
        ;
    }

    public function edit(Genre $genre)
    {
        return view('genres.edit', compact('genre'));
    }

    public function update(GenreRequest $request, Genre $genre): RedirectResponse
    {
        $genre->update($request->validated());
        return redirect()->route('genres.index', $genre)->with('success', 'ジャンルを更新しました。');
    }

    public function destroy(Genre $genre): RedirectResponse
    {
        if ($genre->books()->exists()) {
            // 書籍が紐付いている場合は削除を制限し、エラーメッセージとともに戻る
            return redirect()->route('genres.index')
                ->with('error', '書籍が紐付いているため、このジャンルは削除できません。');
        }

        $genre->delete();

        return redirect()->route('genres.index')->with('success', 'ジャンルを削除しました。');
    }
}
