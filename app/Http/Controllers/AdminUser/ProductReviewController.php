<?php

namespace App\Http\Controllers\AdminUser;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function index()
    {
        $reviews = ProductReview::with('user', 'product')
            ->latest()
            ->get();

        return response()->json([
            'reviews' => $reviews,
        ], 200);
    }

    public function show($id)
    {
        $review = ProductReview::with('user', 'product')->find($id);

        if (!$review) {
            return response()->json([
                'message' => 'Ulasan tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'review' => $review,
        ], 200);
    }
}
