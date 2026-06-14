<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Logging\AppLogger;
use App\Services\Admin\ProductsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Controller for admin product management pages.
 *
 * Includes listing, adding, and editing products.
 * Uses ProductTableService for data retrieval and pagination.
 */
class ProductsController extends Controller
{
    public function __construct(
        protected AppLogger $logger,
        private readonly ProductsService $productsService,
    ) {
        $this->logger = $logger;
        $this->logger->setContext(context: 'AdminProductsController Web');
    }

    /**
     * Admin Products listing page with pagination and search filters.
     */
    public function index(Request $request): Factory|View
    {
        $paginate = $this->productsService->paginate($request);
        $data = collect(
            ProductResource::collection($paginate->items())->resolve($request)
        )->map(function (array $product) {
            return json_decode(json_encode($product));
        });

        return view('auth.admin.sections.products.index', [
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
     * Admin add product page.
     */
    public function add(): Factory|View
    {
        return view('auth.admin.sections.products.add');
    }

    /**
     * Handle create product form submission.
     */
    public function store(Request $request): Factory|View|RedirectResponse
    {
        $data = $request->validate([
            'slug' => 'required|string|max:120|unique:products,slug',
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:2000',
        ]);

        $product = $this->productsService->create($data);

        // redirect to edit page for the new product with success message
        return redirect()->route('auth.admin.products.edit', ['productUid' => $product->uid])
            ->with('success', 'Product created successfully. You can now edit the details.');
    }

    /**
     * Admin edit product page by product UID.
     */
    public function edit(string $productUid): Factory|View
    {
        $entity = $this->productsService->getByUid($productUid);

        return view('auth.admin.sections.products.edit', [
            'entity' => ProductResource::make($entity),
        ]);
    }

    /**
     * Handle update product form submission.
     */
    public function update(Request $request, string $productUid): Factory|View|RedirectResponse
    {
        $data = $request->validate([
            // Product fields
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:2000',
            // CatalogItem fields
            'catalogItem.title' => 'required|string|max:255',
            'catalogItem.short_description' => 'nullable|string|max:500',
            'catalogItem.description' => 'nullable|string|max:2000',
            'catalogItem.price_amount' => 'required|integer|min:0',
            'catalogItem.currency' => 'required|string|size:3',
            'catalogItem.promotion_percentage' => 'nullable|integer|min:0|max:100',
            'catalogItem.is_published' => 'nullable|boolean',
            'catalogItem.image_url' => 'nullable|string|max:2048',
            'catalogItem.promotion_eligible' => 'nullable|boolean',
            'catalogItem.discount_code_eligible' => 'nullable|boolean',
            'catalogItem.shipping_required' => 'nullable|boolean',
        ]);

        $updated = $this->productsService->update(
            uuid: $productUid,
            data: $data
        );

        // redirect back to edit page with success message
        return redirect()->route('auth.admin.products.edit', ['productUid' => $productUid])
            ->with('success', 'Product updated successfully.');
    }
}
