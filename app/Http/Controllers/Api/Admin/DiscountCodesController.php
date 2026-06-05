<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\DiscountCodeResource;
use App\Models\DiscountCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class DiscountCodesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = max(1, min((int) $request->query('limit', 20), 100));
        $search = trim((string) $request->query('q', ''));

        $query = DiscountCode::query()->latest('updated_at')->latest('id');

        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($nested) use ($like) {
                $nested
                    ->where('code', 'like', $like)
                    ->orWhere('assigned_email', 'like', $like)
                    ->orWhere('internal_note', 'like', $like);
            });
        }

        $discountCodes = $query->paginate($limit)->appends($request->query());

        return response()->json([
            'status' => true,
            'data' => DiscountCodeResource::collection($discountCodes->items()),
            'pagination' => [
                'total' => $discountCodes->total(),
                'per_page' => $discountCodes->perPage(),
                'current_page' => $discountCodes->currentPage(),
                'last_page' => $discountCodes->lastPage(),
                'from' => $discountCodes->firstItem(),
                'to' => $discountCodes->lastItem(),
            ],
        ]);
    }

    public function show(string $discountCodeUid): JsonResponse
    {
        $discountCode = DiscountCode::where('uid', $discountCodeUid)->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => new DiscountCodeResource($discountCode),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'code' => ['required', 'string', 'max:64', 'alpha_dash', Rule::unique('discount_codes', 'code')],
            'discount_percentage' => ['required', 'integer', 'min:1', 'max:99'],
            'max_discount_percentage' => ['nullable', 'integer', 'min:1', 'max:99'],
            'min_order_amount' => ['nullable', 'integer', 'min:1'],
            'product_id' => ['nullable', 'uuid', 'exists:products,uid'],
            'assigned_user_id' => ['nullable', 'uuid', 'exists:users,uid'],
            'assigned_email' => ['nullable', 'email', 'max:120'],
            'is_active' => ['sometimes', 'boolean'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'one_use_per_customer' => ['sometimes', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'internal_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $payload['code'] = strtoupper(trim((string) $payload['code']));
        $payload['assigned_email'] = isset($payload['assigned_email']) ? strtolower((string) $payload['assigned_email']) : null;
        $payload['is_active'] = $payload['is_active'] ?? true;
        $payload['one_use_per_customer'] = $payload['one_use_per_customer'] ?? false;
        $payload['created_by_user_id'] = optional($request->user())->uid;
        $payload['updated_by_user_id'] = optional($request->user())->uid;

        $discountCode = DiscountCode::create($payload);

        return response()->json([
            'status' => true,
            'message' => 'Discount code created.',
            'data' => new DiscountCodeResource($discountCode),
        ], 201);
    }

    public function update(Request $request, string $discountCodeUid): JsonResponse
    {
        $discountCode = DiscountCode::where('uid', $discountCodeUid)->firstOrFail();
        $hasRedemptions = $discountCode->redemptions()->exists();

        $payload = $request->validate([
            'code' => ['sometimes', 'string', 'max:64', 'alpha_dash', Rule::unique('discount_codes', 'code')->ignore($discountCodeUid, 'uid')],
            'discount_percentage' => ['sometimes', 'integer', 'min:1', 'max:99'],
            'max_discount_percentage' => ['nullable', 'integer', 'min:1', 'max:99'],
            'min_order_amount' => ['nullable', 'integer', 'min:1'],
            'product_id' => ['nullable', 'uuid', 'exists:products,uid'],
            'assigned_user_id' => ['nullable', 'uuid', 'exists:users,uid'],
            'assigned_email' => ['nullable', 'email', 'max:120'],
            'is_active' => ['sometimes', 'boolean'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'one_use_per_customer' => ['sometimes', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'internal_note' => ['nullable', 'string', 'max:1000'],
            'disabled_at' => ['nullable', 'date'],
            'disabled_by_user_id' => ['nullable', 'uuid', 'exists:users,uid'],
        ]);

        if ($hasRedemptions) {
            $allowedAfterRedemption = [
                'is_active',
                'expires_at',
                'internal_note',
                'disabled_at',
                'disabled_by_user_id',
            ];

            $attemptedProtectedChanges = array_diff(array_keys($payload), $allowedAfterRedemption);

            if (!empty($attemptedProtectedChanges)) {
                throw ValidationException::withMessages([
                    'discount_code' => ['This discount code has redemptions and only status, expiry, or internal note can be edited.'],
                ]);
            }
        }

        if (array_key_exists('code', $payload)) {
            $payload['code'] = strtoupper(trim((string) $payload['code']));
        }

        if (array_key_exists('assigned_email', $payload)) {
            $payload['assigned_email'] = $payload['assigned_email'] ? strtolower((string) $payload['assigned_email']) : null;
        }

        if (array_key_exists('is_active', $payload) && $payload['is_active'] === false) {
            $payload['disabled_at'] = $payload['disabled_at'] ?? now();
            $payload['disabled_by_user_id'] = optional($request->user())->uid;
        }

        if (array_key_exists('is_active', $payload) && $payload['is_active'] === true) {
            $payload['disabled_at'] = null;
            $payload['disabled_by_user_id'] = null;
        }

        $payload['updated_by_user_id'] = optional($request->user())->uid;

        $discountCode->update($payload);

        return response()->json([
            'status' => true,
            'message' => 'Discount code updated.',
            'data' => new DiscountCodeResource($discountCode->fresh()),
        ]);
    }

    public function disable(Request $request, string $discountCodeUid): JsonResponse
    {
        $discountCode = DiscountCode::where('uid', $discountCodeUid)->firstOrFail();

        $discountCode->update([
            'is_active' => false,
            'disabled_at' => now(),
            'disabled_by_user_id' => optional($request->user())->uid,
            'updated_by_user_id' => optional($request->user())->uid,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Discount code disabled.',
            'data' => new DiscountCodeResource($discountCode->fresh()),
        ]);
    }
}
