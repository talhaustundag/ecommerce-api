<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        // İsim Araması
        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        // Minimum ve Maksimum Fiyat Filtresi
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Kategori Filtresi
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Marka Filtresi
        if ($request->has('brand')) {
            $query->where('brand', 'like', "%{$request->brand}%");
        }

        $products = $query->paginate(10);

        return response()->json([
            'success' => true,
            'message' => count($products).' Tane Ürün listelendi.',
            'data' => $products
        ], 200);
    }

    public function store(ProductRequest $request)
    {
        $product = Product::create([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'description' => $request->description,
            'price'       => $request->price,
            'stock'       => $request->stock,
            'brand'       => $request->brand
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ürün oluşturuldu.',
            'data' => $product
        ], 201);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $product->update([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'description' => $request->description,
            'price'       => $request->price,
            'stock'       => $request->stock,
            'brand'       => $request->brand
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ürün güncellendi.',
            'data' => $product
        ], 200);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ürün silindi.'
        ], 200);
    }

}
