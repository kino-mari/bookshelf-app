<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class RankingController extends Controller
{

    public function index()
    {
        $rankedBooks = Book::withAvg('reviews', 'rating') // 'reviews_avg_rating' カラムを追加
            ->has('reviews')                        // レビューが1件以上ある書籍のみに絞り込み
            ->orderByDesc('reviews_avg_rating')     // 平均評価の降順（高い順）でソート
            ->take(10)                              // TOP10（10件）に制限
            ->get();

        return view('ranking.index', compact('rankedBooks'));
    }
}
