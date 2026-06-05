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
    public function index(): JsonResponse
    {
        $products = Product::withTrashed()->latest('updated_at')->latest('id')->get();

        return response()->json([
            'status' => true,
            'data' => ProductResource::collection($products),
        ]);
    }

    public function show(string $productUid): JsonResponse
    {
        $product = Product::withTrashed()->where('uid', $productUid)->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => new ProductResource($product),
        ]);
    }

    public function store(AdminProductStoreRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['purchase_type'] = $payload['purchase_type'] ?? 'one_time';
        $payload['currency'] = strtolower($payload['currency']);

        $product = Product::create($payload);

        return response()->json([
            'status' => true,
            'message' => 'Product created.',
            'data' => new ProductResource($product),
        ], 201);
    }

    public function update(AdminProductUpdateRequest $request, string $productUid): JsonResponse
    {
        $product = Product::withTrashed()->where('uid', $productUid)->firstOrFail();
        $payload = $request->validated();
        $payload['purchase_type'] = $payload['purchase_type'] ?? $product->purchase_type ?? 'one_time';
        $payload['currency'] = strtolower($payload['currency']);

        $product->update($payload);

        return response()->json([
            'status' => true,
            'message' => 'Product updated.',
            'data' => new ProductResource($product->fresh()),
        ]);
    }

    public function destroy(string $productUid): JsonResponse
    {
        $product = Product::withTrashed()->where('uid', $productUid)->firstOrFail();

        if (!$product->trashed()) {
            $product->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Product soft deleted.',
            'data' => new ProductResource($product->fresh()->loadMissing([])),
        ]);
    }
}
