<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Product",
 *     description="Ürün yönetimi işlemleri"
 * )
 */

class ProductController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Tüm ürünleri listele",
     *     tags={"Product"},
     *
     *     @OA\Parameter(name="search", in="query", description="Ürün adı arama", @OA\Schema(type="string")),
     *     @OA\Parameter(name="min_price", in="query", description="Minimum fiyat", @OA\Schema(type="number")),
     *     @OA\Parameter(name="max_price", in="query", description="Maksimum fiyat", @OA\Schema(type="number")),
     *     @OA\Parameter(name="category_id", in="query", description="Kategori ID filtresi", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="brand", in="query", description="Marka filtresi", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_by", in="query", description="Sıralama", @OA\Schema(type="string")),
     *     @OA\Parameter(name="page", in="query", description="Sayfa numarası", @OA\Schema(type="integer")),
     *
     *     @OA\Response(
     *          response=200,
     *          description="Ürün listesi başarıyla getirildi",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="20 Tane Ürün listelendi."),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  description="Sayfalama ile ürün listesi",
     *                  @OA\Property(property="current_page", type="integer", example=1),
     *                  @OA\Property(
     *                      property="data",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(property="id", type="integer", example=1),
     *                          @OA\Property(property="name", type="string", example="iPhone 15"),
     *                          @OA\Property(property="description", type="string", example="Amiral gemisi telefon"),
     *                          @OA\Property(property="price", type="number", example=49999),
     *                          @OA\Property(property="stock", type="integer", example=10),
     *                          @OA\Property(property="brand", type="string", example="Apple"),
     *                          @OA\Property(property="category_id", type="integer", example=2)
     *                      )
     *                  ),
     *                  @OA\Property(property="total", type="integer", example=150),
     *                  @OA\Property(property="last_page", type="integer", example=8)
     *              ),
     *              @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *          )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Ürün detayı getir",
     *     tags={"Product"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Ürün ID",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Ürün detayı başarıyla getirildi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Ürün detayları getirildi."),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Ürün bulunamadı",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Ürün bulunamadı."),
     *             @OA\Property(property="data", type="string", example=null),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function product_detail($id)
    {
        $product = Product::with('category')->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Ürün bulunamadı.',
                'data' => null,
                'errors' => []
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ürün detayları getirildi.',
            'data' => $product,
            'errors' => []
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/products",
     *     summary="Yeni ürün oluştur (Admin)",
     *     tags={"Product"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","price","stock","category_id"},
     *             @OA\Property(property="name", type="string", example="Samsung S23"),
     *             @OA\Property(property="description", type="string", example="Android telefon"),
     *             @OA\Property(property="price", type="number", example=29999),
     *             @OA\Property(property="stock", type="integer", example=25),
     *             @OA\Property(property="brand", type="string", example="Samsung"),
     *             @OA\Property(property="category_id", type="integer", example=1)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Ürün başarıyla oluşturuldu",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Ürün oluşturuldu."),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validasyon hatası"
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/api/admin/products/{id}",
     *     summary="Ürün güncelle (Admin)",
     *     tags={"Product"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Ürün ID",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="stock", type="integer"),
     *             @OA\Property(property="brand", type="string"),
     *             @OA\Property(property="category_id", type="integer")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Ürün güncellendi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Ürün güncellendi."),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/admin/products/{id}",
     *     summary="Ürün sil (Admin)",
     *     tags={"Product"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Ürün başarıyla silindi",
     *         @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Ürün silindi."),
     *              @OA\Property(property="data", type="array", @OA\Items()),
     *              @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Ürün bulunamadı"
     *     )
     * )
     */
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
