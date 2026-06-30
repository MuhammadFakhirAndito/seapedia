<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\ReviewRequest;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with('user:id,username')
            ->latest()
            ->get()
            ->map(function ($review) {
                return [
                    'id'         => $review->id,
                    'reviewer'   => $review->user
                                    ? $review->user->username
                                    : ($review->guest_name ?? 'Anonymous'),
                    'rating'     => $review->rating,
                    // e() = htmlspecialchars biar cegah XSS pas mau render di frontend
                    'comment'    => e($review->comment),
                    'created_at' => $review->created_at,
                ];
            });

        return response()->json($reviews);
    }

    public function store(ReviewRequest $request)
    {
        $user = $request->user(); 

        $review = Review::create([
            'user_id'    => $user?->id,
            'guest_name' => $user ? null : strip_tags($request->guest_name),
            'rating'     => $request->rating,
            'comment'    => strip_tags($request->comment),
        ]);

        return response()->json([
            'message' => 'Review berhasil dikirim. Terima kasih!',
            'review'  => [
                'id'       => $review->id,
                'reviewer' => $user ? $user->username : ($review->guest_name ?? 'Anonymous'),
                'rating'   => $review->rating,
                'comment'  => e($review->comment),
            ],
        ], 201);
    }
}
