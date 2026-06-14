<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Logging\AppLogger;
use App\Models\DiscountCode;
use App\Services\Admin\DiscountCodesService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Controller for admin discount code management pages.
 *
 * Includes listing, adding, and editing discount codes.
 * Uses DiscountCodesService for data retrieval and pagination.
 */
class DiscountCodesController extends Controller
{
    public function __construct(
        protected AppLogger $logger,
        private readonly DiscountCodesService $discountCodesService,
    ) {
        $this->logger = $logger;
        $this->logger->setContext(context: 'AdminDiscountCodesController Web');
    }

    /**
     * Admin Discount Codes listing page with pagination and search filters.
     */
    public function index(Request $request): Factory|View
    {
        $paginate = $this->discountCodesService->paginate($request);

        return view('auth.admin.sections.discount-codes.index', [
            'data' => $paginate->items(),
            'filters' => [
                'q' => trim((string) $request->query('q', '')),
            ],
            'pagination' => [
                'total' => $paginate->total(),
                'per_page' => $paginate->perPage(),
                'current_page' => $paginate->currentPage(),
                'last_page' => $paginate->lastPage(),
                'from' => $paginate->firstItem(),
                'to' => $paginate->lastItem(),
            ],
        ]);
    }

    /**
     * Admin add discount code page.
     */
    public function add(): Factory|View
    {
        $catalogItems = $this->discountCodesService->catalogItems();

        return view('auth.admin.sections.discount-codes.add', [
            'catalogItems' => $catalogItems,
        ]);
    }

    /**
     * Edit discount code page by discount code UID.
     */
    public function edit(string $discoCodeUid): Factory|View
    {
        $entity = $this->discountCodesService->getByUid($discoCodeUid);
        $catalogItems = $this->discountCodesService->catalogItems();

        return view('auth.admin.sections.discount-codes.edit', [
            'entity' => $entity,
            'catalogItems' => $catalogItems,
        ]);
    }

    /**
     * Create a new discount code.
     *
     * @param  Request  $request  Incoming request with discount code payload.
     *
     * @redirect RedirectResponse to discount code listing page with success message.
     */
    public function store(Request $request)
    {
        $payload = $request->validate([
            'code' => ['required', 'string', 'max:64', 'alpha_dash', Rule::unique('discount_codes', 'code')],
            'discount_percentage' => ['required', 'integer', 'min:1', 'max:99'],
            'max_discount_percentage' => ['nullable', 'integer', 'min:1', 'max:99'],
            'min_order_amount' => ['nullable', 'integer', 'min:1'],
            'catalog_item_id' => ['nullable', 'uuid', 'exists:catalog_items,uid'],
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

        // redirect back to listing page with success message
        return redirect()->route('auth.admin.discount-codes')
            ->with('success', 'Discount code created successfully.');
    }

    /**
     * Handle update usecase
     */
    public function update(Request $request, string $discoCodeUid)
    {
        $payload = $request->validate([
            'code' => ['sometimes', 'string', 'max:64', 'alpha_dash', Rule::unique('discount_codes', 'code')->ignore($discoCodeUid, 'uid')],
            'discount_percentage' => ['sometimes', 'integer', 'min:1', 'max:99'],
            'max_discount_percentage' => ['nullable', 'integer', 'min:1', 'max:99'],
            'min_order_amount' => ['nullable', 'integer', 'min:1'],
            'catalog_item_id' => ['nullable', 'uuid', 'exists:catalog_items,uid'],
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

        $this->discountCodesService->update(
            array_merge($payload, ['uid' => $discoCodeUid])
        );

        // redirect back to edit page with success message
        return redirect()->route('auth.admin.discount-codes.edit', ['discoCodeUid' => $discoCodeUid])
            ->with('success', 'Discount code updated successfully.');
    }
}
