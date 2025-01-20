<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        return Product::all();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:50',
            'category_id' => 'required|exists:categories,id',
            'original_price' => 'required|integer|min:0',
            'sale_price' => 'nullable|integer|min:0',
            'size' => 'required|string|max:20',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ], [
            'product_name.required' => 'Nama produk wajib diisi.',
            'category_id.exists' => 'Kategori tidak ditemukan.',
            'original_price.required' => 'Harga asli produk wajib diisi.',
            'size.required' => 'Ukuran produk wajib diisi.',
            'stock.required' => 'Stok produk wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $product = new Product;
        $product->product_name = $request->product_name;
        $product->category_id = $request->category_id;
        $product->original_price = $request->original_price;
        $product->sale_price = $request->sale_price;
        $product->size = $request->size;
        $product->stock = $request->stock;
        $product->description = $request->description;
        $product->save();

        return response()->json([
            'product' => $product,
            'message' => 'Produk berhasil ditambahkan.',
        ]);
    }

    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'product' => $product,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:50',
            'category_id' => 'required|exists:categories,id',
            'original_price' => 'required|integer|min:0',
            'sale_price' => 'nullable|integer|min:0',
            'size' => 'required|string|max:20',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        $product->product_name = $request->product_name;
        $product->category_id = $request->category_id;
        $product->original_price = $request->original_price;
        $product->sale_price = $request->sale_price;
        $product->size = $request->size;
        $product->stock = $request->stock;
        $product->description = $request->description;
        $product->save();

        return response()->json([
            'product' => $product,
            'message' => 'Produk berhasil diperbarui.',
        ]);
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
        ]);
    }
}
