<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminProductStoreRequest;
use App\Http\Requests\AdminProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductsController extends Controller
{
    /**
     * List products including soft-deleted records.
     *
     * @return JsonResponse Product list response.
     */
    public function index(): JsonResponse
    {
        $products = Product::withTrashed()->latest('updated_at')->latest('id')->get();

        return response()->json([
            'status' => true,
            'data' => ProductResource::collection($products),
        ]);
    }

    /**
     * Return a single product by UID including soft-deleted records.
     *
     * @param  string  $productUid  Product UID.
     * @return JsonResponse Product detail response.
     */
    public function show(string $productUid): JsonResponse
    {
        $product = Product::withTrashed()->where('uid', $productUid)->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * Create a product.
     *
     * @param  AdminProductStoreRequest  $request  Validated product create request.
     * @return JsonResponse Created product response.
     */
    public function store(AdminProductStoreRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['purchase_type'] = $payload['purchase_type'] ?? 'one_time';
        $payload['promotion_percentage'] = (int) ($payload['promotion_percentage'] ?? 0);
        $payload['description'] = $payload['long_description'] ?? ($payload['description'] ?? null);
        $payload['currency'] = strtolower($payload['currency']);

        $product = Product::create($payload);

        return response()->json([
            'status' => true,
            'message' => 'Product created.',
            'data' => new ProductResource($product),
        ], 201);
    }

    /**
     * Update a product by UID.
     *
     * @param  AdminProductUpdateRequest  $request  Validated product update request.
     * @param  string  $productUid  Product UID.
     * @return JsonResponse Updated product response.
     */
    public function update(AdminProductUpdateRequest $request, string $productUid): JsonResponse
    {
        $product = Product::withTrashed()->where('uid', $productUid)->firstOrFail();
        $payload = $request->validated();
        $payload['purchase_type'] = $payload['purchase_type'] ?? $product->purchase_type ?? 'one_time';
        $payload['promotion_percentage'] = (int) ($payload['promotion_percentage'] ?? $product->promotion_percentage ?? 0);
        $payload['description'] = $payload['long_description'] ?? ($payload['description'] ?? $product->description);
        $payload['currency'] = strtolower($payload['currency']);

        $product->update($payload);

        return response()->json([
            'status' => true,
            'message' => 'Product updated.',
            'data' => new ProductResource($product->fresh()),
        ]);
    }

    /**
     * Soft delete a product by UID.
     *
     * @param  string  $productUid  Product UID.
     * @return JsonResponse Soft-deleted product response.
     */
    public function destroy(string $productUid): JsonResponse
    {
        $product = Product::withTrashed()->where('uid', $productUid)->firstOrFail();

        if (! $product->trashed()) {
            $product->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Product soft deleted.',
            'data' => new ProductResource($product->fresh()->loadMissing([])),
        ]);
    }
}
