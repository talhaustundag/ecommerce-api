<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Listeleme
    public function index()
    {
        $categories = Category::orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => count($categories).' Tane Kategori listelendi.',
            'data' => $categories
        ], 200);
    }

    // (Admin)
    public function store(CategoryRequest $request)
    {

        $category = Category::create([
            'name' => $request->name
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori oluşturuldu.',
            'data' => $category
        ], 201);
    }

    // (Admin)
    public function update(CategoryRequest $request, Category $category)
    {
        $category->update([
            'name' => $request->name
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori güncellendi.',
            'data' => $category
        ], 200);
    }

    // (Admin)
    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori silindi.',
            'data' => []
        ], 200);
    }
}
