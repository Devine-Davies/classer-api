<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionTypeResource;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlansController extends Controller
{
    /**
     * List subscription plans.
     */
    public function index(): JsonResponse
    {
        $plans = Plan::query()->with('catalogItem')->latest('updated_at')->latest('id')->get();

        return response()->json([
            'status' => true,
            'data' => SubscriptionTypeResource::collection($plans),
        ]);
    }

    /**
     * Show subscription plan details.
     */
    public function show(string $planUid): JsonResponse
    {
        $plan = Plan::query()->with('catalogItem')->where('uid', $planUid)->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => new SubscriptionTypeResource($plan),
        ]);
    }

    /**
     * Create a subscription plan.
     */
    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:120', Rule::unique('plans', 'code')],
            'quota' => ['nullable', 'integer', 'min:0'],
            'type' => ['nullable', 'string', 'max:120'],
            'duration' => ['nullable', 'string', 'max:255'],
            'catalog_sku' => ['nullable', 'string', 'max:64', Rule::unique('catalog_items', 'sku')],
            'catalog_slug' => ['nullable', 'string', 'max:255', Rule::unique('catalog_items', 'slug')],
            'catalog_price_amount' => ['nullable', 'integer', 'min:0'],
            'catalog_currency' => ['nullable', 'string', 'size:3'],
            'catalog_is_active' => ['nullable', 'boolean'],
            'catalog_image_url' => ['nullable', 'string', 'max:2048'],
            'promotion_eligible' => ['nullable', 'boolean'],
            'discount_code_eligible' => ['nullable', 'boolean'],
            'shipping_required' => ['nullable', 'boolean'],
        ]);

        $payload['uid'] = (string) Str::uuid();
        $payload['code'] = strtolower(trim((string) $payload['code']));
        $payload['type'] = trim((string) ($payload['type'] ?? '')) ?: null;
        $payload['duration'] = trim((string) ($payload['duration'] ?? '')) ?: null;

        $plan = Plan::create([
            'uid' => $payload['uid'],
            'title' => $payload['title'],
            'code' => $payload['code'],
            'quota' => $payload['quota'] ?? null,
            'type' => $payload['type'],
            'duration' => $payload['duration'],
        ]);

        $plan->syncCatalogItem([
            'sku' => trim((string) ($payload['catalog_sku'] ?? '')) ?: null,
            'slug' => trim((string) ($payload['catalog_slug'] ?? '')) ?: null,
            'price_amount' => (int) ($payload['catalog_price_amount'] ?? 0),
            'currency' => strtolower((string) ($payload['catalog_currency'] ?? 'gbp')),
            'is_active' => (bool) ($payload['catalog_is_active'] ?? true),
            'image_url' => trim((string) ($payload['catalog_image_url'] ?? '')) ?: null,
            'promotion_eligible' => (bool) ($payload['promotion_eligible'] ?? true),
            'discount_code_eligible' => (bool) ($payload['discount_code_eligible'] ?? true),
            'shipping_required' => (bool) ($payload['shipping_required'] ?? false),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Plan created.',
            'data' => new SubscriptionTypeResource($plan->fresh()->load('catalogItem')),
        ], 201);
    }

    /**
     * Update a subscription plan.
     */
    public function update(Request $request, string $planUid): JsonResponse
    {
        $plan = Plan::query()->where('uid', $planUid)->firstOrFail();

        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:120', Rule::unique('plans', 'code')->ignore($planUid, 'uid')],
            'quota' => ['nullable', 'integer', 'min:0'],
            'type' => ['nullable', 'string', 'max:120'],
            'duration' => ['nullable', 'string', 'max:255'],
            'catalog_sku' => ['nullable', 'string', 'max:64', Rule::unique('catalog_items', 'sku')->ignore(optional($plan->catalogItem)->uid, 'uid')],
            'catalog_slug' => ['nullable', 'string', 'max:255', Rule::unique('catalog_items', 'slug')->ignore(optional($plan->catalogItem)->uid, 'uid')],
            'catalog_price_amount' => ['nullable', 'integer', 'min:0'],
            'catalog_currency' => ['nullable', 'string', 'size:3'],
            'catalog_is_active' => ['nullable', 'boolean'],
            'catalog_image_url' => ['nullable', 'string', 'max:2048'],
            'promotion_eligible' => ['nullable', 'boolean'],
            'discount_code_eligible' => ['nullable', 'boolean'],
            'shipping_required' => ['nullable', 'boolean'],
        ]);

        $payload['code'] = strtolower(trim((string) $payload['code']));
        $payload['type'] = trim((string) ($payload['type'] ?? '')) ?: null;
        $payload['duration'] = trim((string) ($payload['duration'] ?? '')) ?: null;

        $plan->update([
            'title' => $payload['title'],
            'code' => $payload['code'],
            'quota' => $payload['quota'] ?? null,
            'type' => $payload['type'],
            'duration' => $payload['duration'],
        ]);

        $plan->syncCatalogItem([
            'sku' => trim((string) ($payload['catalog_sku'] ?? '')) ?: null,
            'slug' => trim((string) ($payload['catalog_slug'] ?? '')) ?: null,
            'price_amount' => (int) ($payload['catalog_price_amount'] ?? 0),
            'currency' => strtolower((string) ($payload['catalog_currency'] ?? 'gbp')),
            'is_active' => (bool) ($payload['catalog_is_active'] ?? true),
            'image_url' => trim((string) ($payload['catalog_image_url'] ?? '')) ?: null,
            'promotion_eligible' => (bool) ($payload['promotion_eligible'] ?? true),
            'discount_code_eligible' => (bool) ($payload['discount_code_eligible'] ?? true),
            'shipping_required' => (bool) ($payload['shipping_required'] ?? false),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Plan updated.',
            'data' => new SubscriptionTypeResource($plan->fresh()->load('catalogItem')),
        ]);
    }
}
