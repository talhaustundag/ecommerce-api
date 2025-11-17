<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Category",
 *     description="Kategori yönetimi işlemleri"
 * )
 */


class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/categories",
     *     summary="Tüm kategorileri listele",
     *     tags={"Category"},
     *     @OA\Response(
     *         response=200,
     *         description="Kategori listesi",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="5 Tane Kategori listelendi."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Elektronik"),
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="array",
     *                 @OA\Items(type="string")
     *             )
     *         )
     *     )
     * )
     */

    // Listeleme
    public function index()
    {
        $categories = Category::orderBy('id', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => count($categories).' Tane Kategori listelendi.',
            'data' => $categories,
            'errors' => []
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/categories",
     *     summary="Yeni kategori oluştur (Admin)",
     *     tags={"Category"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Elektronik")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Kategori oluşturuldu"
     *     )
     * )
     */
    // (Admin)
    public function store(CategoryRequest $request)
    {

        $category = Category::create([
            'name' => $request->name
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori oluşturuldu.',
            'data' => $category,
            'errors' => []
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/categories/{id}",
     *     summary="Kategori güncelle (Admin)",
     *     tags={"Category"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Kategori ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Telefonlar")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Kategori güncellendi"
     *     )
     * )
     */
    // (Admin)
    public function update(CategoryRequest $request, Category $category)
    {
        $category->update([
            'name' => $request->name
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori güncellendi.',
            'data' => $category,
            'errors' => []
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/categories/{id}",
     *     summary="Kategori sil (Admin)",
     *     tags={"Category"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Kategori ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Kategori silindi"
     *     )
     * )
     */
    // (Admin)
    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori silindi.',
            'data' => [],
            'errors' => []
        ], 200);
    }
}
