<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CatalogItemResource;
use App\Logging\AppLogger;
use App\Services\Admin\CatalogItemsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Controller for admin catalog item management pages.
 *
 * Includes listing, adding, and editing catalog items.
 * Uses CatalogItemsService for data retrieval and pagination.
 */
class CatalogItemsController extends Controller
{
    public function __construct(
        protected AppLogger $logger,
        private readonly CatalogItemsService $catalogItemsService,
    ) {
        $this->logger = $logger;
        $this->logger->setContext(context: 'AdminCatalogItemsController Web');
    }

    /**
     * Admin Catalog Items listing page with pagination and search filters.
     */
    public function index(Request $request): Factory|View
    {
        $paginate = $this->catalogItemsService->paginate($request);

        return view('auth.admin.sections.catalog-items.index', [
            'data' => CatalogItemResource::collection($paginate->items()),
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
     * Admin add catalog item page.
     */
    public function add(): Factory|View
    {
        $products = $this->catalogItemsService->getAllProducts();
        $plans = $this->catalogItemsService->getAllPlans();

        return view('auth.admin.sections.catalog-items.add', [
            'products' => $products,
            'plans' => $plans,
        ]);
    }

    /**
     * Handle store usecase
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'sellable_type' => 'required|string|max:255',
            'sellable_id' => 'required|integer',
            'sku' => 'required|string|max:64|unique:catalog_items,sku',
            'slug' => 'required|string|max:255|unique:catalog_items,slug',
            'title' => 'required|string|max:255',
            'price_amount' => 'required|integer|min:0',
            'currency' => 'required|string|size:3',
            'is_published' => 'nullable|boolean',
            'image_url' => 'nullable|string|max:2048',
            'promotion_eligible' => 'nullable|boolean',
            'discount_code_eligible' => 'nullable|boolean',
            'shipping_required' => 'nullable|boolean',
        ]);

        $this->catalogItemsService->create($data);

        // redirect back to listing page with success message
        return redirect()->route('auth.admin.catalog-items')
            ->with('success', 'Catalog item created successfully.');
    }

    /**
     * Admin edit catalog item page by catalog item UID.
     */
    public function edit(string $catUid): Factory|View
    {
        $entity = $this->catalogItemsService->getByUid($catUid);
        $products = $this->catalogItemsService->getAllProducts();
        $plans = $this->catalogItemsService->getAllPlans();

        return view('auth.admin.sections.catalog-items.edit', [
            'entity' => $entity,
            'products' => $products,
            'plans' => $plans,
        ]);
    }

    /**
     * Handle update usecase
     */
    public function update(Request $request, string $catUid): Factory|View|RedirectResponse
    {
        $data = $request->validate([
            'sellable_type' => 'required|string|max:255',
            'sellable_id' => 'required|integer',
            'slug' => 'required|string|max:255|unique:catalog_items,slug,'.$catUid.',uid',
            'title' => 'required|string|max:255',
            'price_amount' => 'required|integer|min:0',
            'promotion_percentage' => 'nullable|integer|min:0|max:100',
            'currency' => 'required|string|size:3',
            'is_published' => 'nullable|boolean',
            'image_url' => 'nullable|string|max:2048',
            'promotion_eligible' => 'nullable|boolean',
            'discount_code_eligible' => 'nullable|boolean',
            'shipping_required' => 'nullable|boolean',
        ]);

        $this->catalogItemsService->update(
            array_merge($data, ['uid' => $catUid]),
        );

        // redirect back to edit page with success message
        return redirect()->route('auth.admin.catalog-items.edit', ['catUid' => $catUid])
            ->with('success', 'Updated successfully.');
    }
}
