<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * マイ読書レポート画面を表示する
     *
     * ユーザーの読書に関する統計情報（基本サマリー、評価分布、高評価書籍TOP5、ジャンル別評価傾向TOP5）を集計して渡す。
     *
     * @param Request $request リクエストインスタンス
     * @return View マイ読書レポート画面のビュー
     */
    public function index(Request $request): View
    {
        $userId = Auth::id();

        $totalReviews = Review::where('user_id', $userId)
            ->count();

        $readBooks = Review::where('user_id', $userId)
            ->distinct()
            ->count('book_id');

        $averageRating = Review::where('user_id', $userId)
            ->avg('rating');

        $ratingCounts = Review::where('user_id', $userId)
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $ratingDistribution = collect(range(1, 5))
            ->mapWithKeys(function (int $rating) use ($ratingCounts): array {
                return [$rating => $ratingCounts[$rating] ?? 0];
            });

        $topBooks = Book::query()
            ->join('reviews', 'books.id', '=', 'reviews.book_id')
            ->where('reviews.user_id', $userId)
            ->where('reviews.rating', '>=', 4)
            ->select('books.*')
            ->selectRaw('MAX(reviews.rating) as user_rating')
            ->groupBy('books.id')
            ->orderByDesc('user_rating')
            ->limit(5)
            ->get();

        $genreRatings = Review::query()
            ->join('books', 'reviews.book_id', '=', 'books.id')
            ->join('book_genre', 'books.id', '=', 'book_genre.book_id')
            ->join('genres', 'book_genre.genre_id', '=', 'genres.id')
            ->where('reviews.user_id', $userId)
            ->select(
                'genres.id',
                'genres.name'
            )
            ->selectRaw('AVG(reviews.rating) as average_rating')
            ->selectRaw('COUNT(reviews.id) as review_count')
            ->groupBy('genres.id', 'genres.name')
            ->orderByDesc('average_rating')
            ->limit(5)
            ->get();

        return view('reports.index', compact(
            'totalReviews',
            'readBooks',
            'averageRating',
            'ratingDistribution',
            'topBooks',
            'genreRatings'
        ));
    }
}