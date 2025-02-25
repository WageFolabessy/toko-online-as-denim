<?php

namespace App\Http\Controllers\AdminUser;

use App\Http\Requests\CategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('created_at', 'desc')->get();
        return response()->json($categories, 200);
    }

    public function store(CategoryRequest $request)
    {
        try {
            $data = $request->validated();

            // Menyimpan gambar kategori
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('categories', 'public');
            }

            $category = Category::create($data);

            return response()->json([
                'category' => $category,
                'message'  => 'Kategori berhasil ditambahkan.'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message'  => 'Terjadi kesalahan saat menambahkan kategori.',
                'error'    => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        // Menggunakan route model binding jika memungkinkan
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Kategori tidak ditemukan.',
            ], 404);
        }

        return response()->json($category, 200);
    }

    public function update(UpdateCategoryRequest $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Kategori tidak ditemukan.',
            ], 404);
        }

        $data = $request->validated();

        // Menghapus gambar lama jika ada gambar baru
        if ($request->hasFile('image')) {
            if (Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return response()->json([
            'category' => $category,
            'message'  => 'Kategori berhasil diperbarui.',
        ], 200);
    }

    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Kategori tidak ditemukan.',
            ], 404);
        }

        // Menghapus gambar kategori
        if (Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return response()->json([
            'message' => 'Kategori berhasil dihapus.',
        ], 200);
    }
}
