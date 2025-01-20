<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::all();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|unique:categories,category_name',
            'parent_category_id' => 'nullable|exists:categories,id'
        ], [
            'category_name.required' => 'Nama kategori wajib diisi.',
            'category_name.unique' => 'Nama kategori sudah terdaftar.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $category = new Category;
        $category->category_name = $request->category_name;
        $category->parent_category_id = $request->parent_category_id;
        $category->save();

        return response()->json([$category, 'message' => 'Kategori berhasil ditambahkan.']);
    }

    public function show($id)
    {
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
            'category_name' => 'required|unique:categories,category_name,' . $id,
            'parent_category_id' => 'nullable|exists:categories,id'
        ], [
            'category_name.required' => 'Nama kategori wajib diisi.',
            'category_name.unique' => 'Nama kategori sudah terdaftar.',
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

        $category->category_name = $request->category_name;
        $category->parent_category_id = $request->parent_category_id;
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

        $category->delete();

        return response()->json([
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }
}
