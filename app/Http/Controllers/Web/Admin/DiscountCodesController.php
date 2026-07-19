<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Admin\DiscountCodeCreateRequest;
use App\Http\Requests\Web\Admin\DiscountCodeUpdateRequest;
use App\Http\Resources\DiscountCodeResource;
use App\Logging\AppLogger;
use App\Models\DiscountCode;
use App\Services\Admin\DiscountCodesService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

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

        $data = DiscountCodeResource::collection($paginate->getCollection())
            ->resolve($request);

        $data = collect($data)->map(
            fn (array $discountCode) => json_decode(json_encode($discountCode))
        );

        return view('admin.discount-codes.index', [
            'data' => $data,
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

        return view('admin.discount-codes.add', [
            'catalogItems' => $catalogItems,
        ]);
    }

    /**
     * Edit discount code page by discount code UID.
     */
    public function edit(string $discountCodeUid): Factory|View
    {
        $entity = $this->discountCodesService->getByUid($discountCodeUid);
        $catalogItems = $this->discountCodesService->catalogItems();
        $discountCode = DiscountCodeResource::make($entity)->resolve(request());

        return view('admin.discount-codes.edit', [
            'entity' => json_decode(json_encode($discountCode)),
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
    public function store(DiscountCodeCreateRequest $request)
    {
        $discountCode = DiscountCode::create(
            $request->discountCodePayload(),
        );

        // redirect back to listing page with success message
        $withMessage = $discountCode
            ? ['success' => 'Discount code created successfully. You can now edit the details.']
            : ['error' => 'Failed to create the discount code. Please try again.'];

        return redirect()->route('admin.discount-codes.edit', ['discountCodeUid' => $discountCode->uid])
            ->with($withMessage);
    }

    /**
     * Handle update usecase.
     */
    public function update(DiscountCodeUpdateRequest $request, string $discountCodeUid)
    {
        $updated = $this->discountCodesService->update(
            array_merge($request->payload(), [
                'uid' => $discountCodeUid,
            ])
        );

        $withMessage = $updated
            ? ['success' => 'Updated successfully.']
            : ['error' => 'Failed to update the discount code. Please try again.'];

        return redirect()
            ->route('admin.discount-codes.edit', [
                'discountCodeUid' => $discountCodeUid,
            ])
            ->with($withMessage);
    }
}
