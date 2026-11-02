<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * マイ読書レポート画面を表示する
     *
     * @param  Request  $request  リクエストインスタンス
     * @return View マイ読書レポート画面
     */
    public function index(Request $request): View
    {
        $userId = Auth::id();

        $totalReviews = Review::where('user_id', $userId)->count();

        $readBooks = Review::where('user_id', $userId)
            ->distinct()
            ->count('book_id');

        $averageRating = (float) (
            Review::where('user_id', $userId)->avg('rating') ?? 0
        );

        // 評価ごとのレビュー件数
        $ratingCounts = Review::where('user_id', $userId)
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $ratingDistribution = collect(range(1, 5))
            ->mapWithKeys(function (int $rating) use ($ratingCounts): array {
                return [$rating => $ratingCounts[$rating] ?? 0];
            });

        // 高評価書籍TOP5
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

        // ジャンル別評価TOP5
        $genreRatings = Review::query()
            ->join('books', 'reviews.book_id', '=', 'books.id')
            ->join('book_genre', 'books.id', '=', 'book_genre.book_id')
            ->join('genres', 'book_genre.genre_id', '=', 'genres.id')
            ->where('reviews.user_id', $userId)
            ->select('genres.id', 'genres.name')
            ->selectRaw('AVG(reviews.rating) as average_rating')
            ->selectRaw('COUNT(reviews.id) as review_count')
            ->groupBy('genres.id', 'genres.name')
            ->orderByDesc('average_rating')
            ->limit(5)
            ->get();

        $stats = [
            'summary' => [
                'total_reviews' => $totalReviews,
                'books_read' => $readBooks,
                'average_rating' => $averageRating,
            ],

            'rating_distribution' => $ratingDistribution,

            'top_rated_books' => $topBooks->map(function ($book) {
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->author,
                    'rating' => (int) $book->user_rating,
                ];
            }),

            'genre_ratings' => $genreRatings->map(function ($genre) {
                return [
                    'id' => $genre->id,
                    'name' => $genre->name,
                    'count' => (int) $genre->review_count,
                    'average_rating' => (float) $genre->average_rating,
                ];
            }),
        ];

        return view('reports.index', compact('stats'));
    }
}
