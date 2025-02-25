<?php

namespace App\Http\Controllers\SiteUser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductReview;
use App\Models\Product;
use App\Models\OrderItem;

class ProductReviewController extends Controller
{
    public function index($productId)
    {
        // Pastikan produk ada
        $product = Product::find($productId);
        if (!$product) {
            return response()->json([
                'message' => 'Produk tidak ditemukan.'
            ], 404);
        }

        // Ambil review dengan relasi user (siteUser)
        $reviews = ProductReview::with('user')
            ->where('product_id', $productId)
            ->latest()
            ->get();

        return response()->json([
            'reviews' => $reviews,
        ], 200);
    }

    public function store(Request $request, $productId)
    {
        // Validasi input review
        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string',
        ], [
            'rating.integer'  => 'Rating harus berupa angka.',
            'rating.min'      => 'Rating minimal 1.',
            'rating.max'      => 'Rating maksimal 5.',
        ]);

        // Pastikan produk ada
        $product = Product::find($productId);
        if (!$product) {
            return response()->json([
                'message' => 'Produk tidak ditemukan.'
            ], 404);
        }

        // Pastikan user telah login
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'message' => 'Anda harus login untuk memberikan review.'
            ], 401);
        }

        // Pastikan user telah membeli produk ini dan order-nya sudah delivered.
        $hasPurchased = OrderItem::whereHas('order', function ($query) use ($user) {
            $query->where('site_user_id', $user->id)
                ->where('status', 'delivered');
        })
            ->where('product_id', $productId)
            ->exists();

        if (!$hasPurchased) {
            return response()->json([
                'message' => 'Anda belum membeli produk ini atau produk belum diterima.'
            ], 403);
        }

        // Simpan review baru tanpa opsi anonymous (gunakan nama asli user)
        $review = ProductReview::create([
            'site_user_id' => $user->id,
            'product_id'   => $productId,
            'rating'       => $data['rating'],
            'review'       => $data['review'],
        ]);

        return response()->json([
            'message' => 'Review berhasil ditambahkan.',
            'review'  => $review,
        ], 201);
    }

    public function updateReview(Request $request, $reviewId)
    {
        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string',
        ], [
            'rating.required' => 'Rating harus diisi.',
        ]);

        // Pastikan user telah login
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'message' => 'Anda harus login untuk mengubah review.'
            ], 401);
        }

        // Temukan review berdasarkan ID
        $review = ProductReview::find($reviewId);
        if (!$review) {
            return response()->json([
                'message' => 'Review tidak ditemukan.'
            ], 404);
        }

        // Cek apakah review tersebut milik user yang sedang login
        if ($review->site_user_id != $user->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki izin untuk mengubah review ini.'
            ], 403);
        }

        // Update review
        $review->update([
            'rating' => $data['rating'],
            'review' => $data['review'],
        ]);

        return response()->json([
            'message' => 'Review berhasil diperbarui.',
            'review'  => $review,
        ], 200);
    }

    public function destroyReview(Request $request, $reviewId)
    {
        // Pastikan user telah login
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'message' => 'Anda harus login untuk menghapus review.'
            ], 401);
        }

        // Temukan review berdasarkan ID
        $review = ProductReview::find($reviewId);
        if (!$review) {
            return response()->json([
                'message' => 'Review tidak ditemukan.'
            ], 404);
        }

        // Pastikan review milik user
        if ($review->site_user_id != $user->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki izin untuk menghapus review ini.'
            ], 403);
        }

        $review->delete();
        return response()->json([
            'message' => 'Review berhasil dihapus.'
        ], 200);
    }

    public function reviewEligibility(Request $request, $productId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['eligible' => false], 200);
        }

        $hasPurchased = OrderItem::whereHas('order', function ($query) use ($user) {
            $query->where('site_user_id', $user->id)
                ->where('status', 'delivered');
        })
            ->where('product_id', $productId)
            ->exists();

        return response()->json(['eligible' => $hasPurchased], 200);
    }
}
