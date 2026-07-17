<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Admin\ProductCreateRequest;
use App\Http\Requests\Web\Admin\ProductUpdateRequest;
use App\Http\Resources\Web\Admin\ProductResource;
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

        return view('admin.products.index', [
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
        return view('admin.products.add');
    }

    /**
     * Handle create product form submission.
     */
    public function store(ProductCreateRequest $request): RedirectResponse
    {
        $product = $this->productsService->create(
            $request->productPayload()
        );

        $withMessage = $product
            ? ['success' => 'Product has been created successfully and a catalog item has been created for it. You can now edit these details.']
            : ['error' => 'Failed to create the product. Please try again.'];

        // redirect to edit page for the new product with success message
        return redirect()->route('admin.products.edit', ['productUid' => $product->uid])
            ->with($withMessage);
    }

    /**
     * Admin edit product page by product UID.
     */
    public function edit(string $productUid): Factory|View
    {
        $entity = $this->productsService->getByUid($productUid);

        return view('admin.products.edit', [
            'entity' => ProductResource::make($entity),
        ]);
    }

    /**
     * Handle update product form submission.
     */
    public function update(ProductUpdateRequest $request, string $productUid): Factory|View|RedirectResponse
    {
        $viewMessage = ['error' => 'Failed to update the product. Please try again.'];

        try {
            $updated = $this->productsService->update(
                $productUid,
                $request->planPayload()
            );

            if ($updated) {
                $viewMessage = ['success' => 'Updated successfully.'];
            }
        } catch (\Exception $e) {
            $this->logger->error('Error updating product', [
                'productUid' => $productUid,
                'exception' => $e->getMessage(),
            ]);
        }

        return redirect()->route('admin.products.edit', ['productUid' => $productUid])
            ->with($viewMessage);
    }
}
