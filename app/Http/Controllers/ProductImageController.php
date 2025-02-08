<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductImage;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    // Display all product images
    public function index()
    {
        $productImages = ProductImage::with('product')->get();
        return response()->json($productImages);
    }

    // Display images for a specific product
    public function showByProduct($productId)
    {
        $images = ProductImage::where('product_id', $productId)->get();
        return response()->json($images);
    }

    // Store a new product image
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_primary' => 'nullable|boolean',
        ]);

        // Pastikan field images tidak kosong
        if (!$request->hasFile('images')) {
            return response()->json(['error' => 'No images were uploaded'], 400);
        }

        $uploadedImages = [];

        foreach ($request->file('images') as $image) {
            $imagePath = $image->store('product', 'public');

            if ($request->input('is_primary')) {
                ProductImage::where('product_id', $request->product_id)->update(['is_primary' => false]);
            }

            $productImage = ProductImage::create([
                'product_id' => $request->product_id,
                'image' => $imagePath,
                'is_primary' => $request->input('is_primary', false),
            ]);

            $uploadedImages[] = $productImage;
        }

        return response()->json([
            'message' => 'Gambar berhasil diunggah.',
        ], 201);
    }

    // Update a product image
    public function update(Request $request, $id)
    {
        $productImage = ProductImage::findOrFail($id);

        // Validasi input
        $request->validate([
            'is_primary' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Perbarui status is_primary jika dikirim
        if ($request->has('is_primary') && $request->input('is_primary')) {
            // Pastikan hanya ada satu gambar utama per produk
            ProductImage::where('product_id', $productImage->product_id)->update(['is_primary' => false]);
            $productImage->is_primary = true;
        } elseif ($request->has('is_primary')) {
            // Jika is_primary dikirim tetapi false
            $productImage->is_primary = false;
        }

        // Perbarui gambar jika file dikirim
        if ($request->hasFile('image')) {
            // Hapus file gambar lama
            if ($productImage->image) {
                Storage::disk('public')->delete($productImage->image);
            }

            // Simpan file gambar baru
            $imagePath = $request->file('image')->store('product_images', 'public');
            $productImage->image = $imagePath;
        }

        $productImage->save();

        return response()->json([
            'message' => 'Product image updated successfully.',
            'data' => $productImage,
        ]);
    }
    
    // Delete a product image
    public function destroy($id)
    {
        $productImage = ProductImage::findOrFail($id);

        // Delete the image file from storage
        Storage::disk('public')->delete($productImage->image);

        $productImage->delete();

        return response()->json(['message' => 'Product image deleted successfully.']);
    }
}
