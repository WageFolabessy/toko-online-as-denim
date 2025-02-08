<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::all();
    }

    public function store(Request $request)
    {
        // Validasi data yang diterima
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:50|unique:categories,category_name',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'category_name.required' => 'Nama kategori wajib diisi.',
            'category_name.unique' => 'Nama kategori sudah terdaftar.',
            'image.required' => 'Gambar kategori wajib diunggah.',
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.max' => 'Ukuran gambar terlalu besar. Maksimal 2MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        // Menyimpan gambar kategori
        $imagePath = $request->file('image')->store('categories', 'public');

        // Menyimpan data kategori
        $category = new Category;
        $category->category_name = $request->category_name;
        $category->image = $imagePath;
        $category->save();

        return response()->json([
            'category' => $category,
            'message' => 'Kategori berhasil ditambahkan.',
        ]);
    }

    public function edit($id)
    {
        // Mencari kategori berdasarkan ID
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Kategori tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'category' => $category,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:50|unique:categories,category_name,' . $id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'category_name.required' => 'Nama kategori wajib diisi.',
            'category_name.unique' => 'Nama kategori sudah terdaftar.',
            'image.required' => 'Gambar kategori wajib diunggah.',
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.max' => 'Ukuran gambar terlalu besar. Maksimal 2MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Kategori tidak ditemukan.',
            ], 404);
        }

        if ($request->hasFile('image')) {
            if (file_exists(public_path('storage/' . $category->image))) {
                unlink(public_path('storage/' . $category->image));
            }

            $imagePath = $request->file('image')->store('categories', 'public');
            $category->image = $imagePath;
        }

        $category->category_name = $request->category_name;
        $category->save();


        return response()->json([
            'category' => $category,
            'message' => 'Kategori berhasil diperbarui.',
        ]);
    }

    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Kategori tidak ditemukan.',
            ], 404);
        }

        if (file_exists(public_path('storage/' . $category->image))) {
            unlink(public_path('storage/' . $category->image));
        }

        $category->delete();

        return response()->json([
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }

    public function getByCategory($slug)
    {
        $category = Category::where('slug', $slug)->first();

        if (!$category) {
            return response()->json([
                'message' => 'Kategori tidak ditemukan.',
            ], 404);
        }

        $category->products;

        return response()->json([
            'category' => $category,
        ]);
    }
}
