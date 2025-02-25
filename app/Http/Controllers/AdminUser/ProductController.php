<?php

namespace App\Http\Controllers\AdminUser;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['images', 'category'])->orderBy('created_at', 'desc')->get();
        return response()->json($products, 200);
    }

    public function store(StoreProductRequest $request)
    {
        // Obtain validated data
        $data = $request->validated();

        // Create the product without the slug
        $product = Product::create($data);

        // Handle images if present
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $imagePath = $image->store('product', 'public');

                // Set the first image as primary
                $isPrimary = $index === 0 ? true : false;

                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $imagePath,
                    'is_primary' => $isPrimary,
                ]);
            }
        }

        return response()->json([
            'product' => $product->load('images'),
            'message' => 'Produk berhasil ditambahkan.',
        ], 201);
    }

    public function show($id)
    {
        $product = Product::with(['images', 'category'])->find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'product' => $product,
        ], 200);
    }

    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        $data = $request->validated();

        $product->update($data);

        // Delete images that are marked for deletion
        if ($request->has('imagesToDelete')) {
            $imagesToDelete = $request->input('imagesToDelete');
            foreach ($imagesToDelete as $imageId) {
                $productImage = ProductImage::find($imageId);
                if ($productImage && $productImage->product_id == $product->id) {
                    // Delete image file
                    Storage::disk('public')->delete($productImage->image);
                    // Delete database record
                    $productImage->delete();
                }
            }
        }

        // Handle new images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imageFile) {
                $imagePath = $imageFile->store('product', 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $imagePath,
                    'is_primary' => false, // You can adjust this as needed
                ]);
            }
        }

        // Ensure at least one image is set as primary
        if (!$product->images()->where('is_primary', true)->exists()) {
            $firstImage = $product->images()->first();
            if ($firstImage) {
                $firstImage->update(['is_primary' => true]);
            }
        }

        return response()->json([
            'product' => $product->load('images'),
            'message' => 'Produk berhasil diperbarui.',
        ], 200);
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        $product->delete();

        return response()->json([
            'message' => 'Produk berhasil dihapus.',
        ], 200);
    }
    
    public function getProductDetail($slug)
    {
        $product = Product::with('images')->where('slug', $slug)->first();

        if (!$product) {
            return response()->json([
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'product' => $product,
        ], 200);
    }
}
