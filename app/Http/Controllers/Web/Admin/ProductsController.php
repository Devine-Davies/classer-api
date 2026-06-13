<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Logging\AppLogger;
use App\Services\Admin\ProductsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

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
        return view('auth.admin.sections.products.index', [
            'data' => ProductResource::collection($paginate->items()),
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
     * Admin edit product page by product UID.
     */
    public function edit(string $productUid): Factory|View
    {
        $entity = $this->productsService->findByUid($productUid);
        return view('auth.admin.sections.products.edit', [
            'entity' => $entity,
        ]);
    }

    /**
     * Handle create product form submission.
     */
    public function create(Request $request): Factory|View|RedirectResponse
    {
        $data = $request->validate([
            'sku' => 'required|string|max:120|unique:products,sku',
            'slug' => 'required|string|max:120|unique:products,slug',
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'long_description' => 'nullable|string|max:2000',
            'description' => 'nullable|string|max:2000',
            'image_url' => 'nullable|url|max:1000',
            'is_active' => 'required|boolean',
        ]);

        $product = $this->productsService->create($data);

        // redirect to edit page for the new product with success message
        return redirect()->route('auth.admin.products.edit', ['productUid' => $product->uid])
            ->with('success', 'Product created successfully. You can now edit the details.');
    }

    /**
     * Handle update product form submission.
     */
    public function update(Request $request, string $productUid): Factory|View|RedirectResponse 
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'long_description' => 'nullable|string|max:2000',
            'description' => 'nullable|string|max:2000',
            'image_url' => 'nullable|url|max:1000',
            'is_active' => 'required|boolean',
        ]);

        $updated = $this->productsService->update(
            array_merge($data, ['uid' => $productUid])
        );
        
        // redirect back to edit page with success message
        return redirect()->route('auth.admin.products.edit', ['productUid' => $productUid])
            ->with('success', 'Product updated successfully.');
    }
}
