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
            $query->where('name', 'ilike', "%{$request->search}%");
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
            $query->where('brand', 'ilike', "%{$request->brand}%");
        }
        // Sıralama
        if ($request->has('sort_by')) {
            switch ($request->sort_by) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        }

        $products = $query->paginate(20);

        return response()->json([
            'success' => true,
            'message' => count($products).' Tane Ürün listelendi.',
            'data' => $products,
            'errors' => [],
            'page' => $products->currentPage()
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
            'data' => $product,
            'errors' => []
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
            'data' => $product,
            'errors' => []
        ], 200);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ürün silindi.',
            'data' => [],
            'errors' => []
        ], 200);
    }

}
