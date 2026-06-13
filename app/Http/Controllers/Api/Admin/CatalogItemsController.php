<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CatalogItemResource;
use App\Models\CatalogItem;
use App\Models\Plan;
use App\Models\Product;
use App\Services\Admin\CatalogItemTableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CatalogItemsController extends Controller
{
    public function __construct(private readonly CatalogItemTableService $catalogItemTableService) {}

    /**
     * List catalog items with optional filters and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $items = $this->catalogItemTableService->paginate($request);

        return response()->json([
            'status' => true,
            'data' => CatalogItemResource::collection($items->items()),
            'filters' => [
                'q' => trim((string) $request->query('q', '')),
                'sellable_type' => trim((string) $request->query('sellable_type', 'all')),
                'is_active' => trim((string) $request->query('is_active', 'all')),
            ],
            'pagination' => [
                'total' => $items->total(),
                'per_page' => $items->perPage(),
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
            ],
        ]);
    }

    /**
     * Return a single catalog item by UID.
     */
    public function show(string $catalogItemUid): JsonResponse
    {
        $item = CatalogItem::query()
            ->with('sellable')
            ->where('uid', $catalogItemUid)
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => new CatalogItemResource($item),
        ]);
    }

    /**
     * Create a catalog item.
     */
    public function store(Request $request): JsonResponse
    {
        $allowedSellableTypes = [Product::class, Plan::class];

        $payload = $request->validate([
            'sellable_type' => ['required', 'string', Rule::in($allowedSellableTypes)],
            'sellable_id' => ['required', 'uuid'],
            'sku' => ['required', 'string', 'max:64', Rule::unique('catalog_items', 'sku')],
            'slug' => ['required', 'string', 'max:255', Rule::unique('catalog_items', 'slug')],
            'title' => ['required', 'string', 'max:255'],
            'price_amount' => ['required', 'integer', 'min:0'],
            'promotion_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'currency' => ['required', 'string', 'size:3'],
            'is_active' => ['required', 'boolean'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'promotion_eligible' => ['required', 'boolean'],
            'discount_code_eligible' => ['required', 'boolean'],
            'shipping_required' => ['required', 'boolean'],
        ]);

        $this->assertSellableExists($payload['sellable_type'], $payload['sellable_id']);
        $this->assertSellableIsAvailable($payload['sellable_type'], $payload['sellable_id']);

        $item = CatalogItem::create([
            ...$payload,
            'promotion_percentage' => (int) ($payload['promotion_percentage'] ?? 0),
            'currency' => strtolower((string) $payload['currency']),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Catalog item created.',
            'data' => new CatalogItemResource($item->fresh()->load('sellable')),
        ], 201);
    }

    /**
     * Update a catalog item.
     */
    public function update(Request $request, string $catalogItemUid): JsonResponse
    {
        $item = CatalogItem::query()->where('uid', $catalogItemUid)->firstOrFail();
        $allowedSellableTypes = [Product::class, Plan::class];

        $payload = $request->validate([
            'sellable_type' => ['required', 'string', Rule::in($allowedSellableTypes)],
            'sellable_id' => ['required', 'uuid'],
            'sku' => ['required', 'string', 'max:64', Rule::unique('catalog_items', 'sku')->ignore($catalogItemUid, 'uid')],
            'slug' => ['required', 'string', 'max:255', Rule::unique('catalog_items', 'slug')->ignore($catalogItemUid, 'uid')],
            'title' => ['required', 'string', 'max:255'],
            'price_amount' => ['required', 'integer', 'min:0'],
            'promotion_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'currency' => ['required', 'string', 'size:3'],
            'is_active' => ['required', 'boolean'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'promotion_eligible' => ['required', 'boolean'],
            'discount_code_eligible' => ['required', 'boolean'],
            'shipping_required' => ['required', 'boolean'],
        ]);

        $this->assertSellableExists($payload['sellable_type'], $payload['sellable_id']);
        $this->assertSellableIsAvailable($payload['sellable_type'], $payload['sellable_id'], $item->uid);

        $item->update([
            ...$payload,
            'promotion_percentage' => (int) ($payload['promotion_percentage'] ?? 0),
            'currency' => strtolower((string) $payload['currency']),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Catalog item updated.',
            'data' => new CatalogItemResource($item->fresh()->load('sellable')),
        ]);
    }

    /**
     * Return products/plans reference options for admin forms.
     */
    public function options(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'sellable_types' => [
                Product::class,
                Plan::class,
            ],
            'products' => Product::query()
                ->select(['uid', 'name', 'slug'])
                ->orderBy('name')
                ->get()
                ->map(fn (Product $product) => [
                    'uid' => $product->uid,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'type' => Product::class,
                ])
                ->values(),
            'plans' => Plan::query()
                ->select(['uid', 'title', 'code'])
                ->orderBy('title')
                ->get()
                ->map(fn (Plan $plan) => [
                    'uid' => $plan->uid,
                    'title' => $plan->title,
                    'code' => $plan->code,
                    'type' => Plan::class,
                ])
                ->values(),
        ]);
    }

    private function assertSellableExists(string $sellableType, string $sellableId): void
    {
        $exists = match ($sellableType) {
            Product::class => Product::query()->where('uid', $sellableId)->exists(),
            Plan::class => Plan::query()->where('uid', $sellableId)->exists(),
            default => false,
        };

        if (! $exists) {
            throw ValidationException::withMessages([
                'sellable_id' => ['Sellable entity does not exist for the selected type.'],
            ]);
        }
    }

    private function assertSellableIsAvailable(string $sellableType, string $sellableId, ?string $ignoreCatalogItemUid = null): void
    {
        $query = CatalogItem::query()
            ->where('sellable_type', $sellableType)
            ->where('sellable_id', $sellableId);

        if ($ignoreCatalogItemUid !== null) {
            $query->where('uid', '!=', $ignoreCatalogItemUid);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'sellable_id' => ['This sellable already has a catalog item.'],
            ]);
        }
    }
}
