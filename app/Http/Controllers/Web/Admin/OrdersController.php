<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Logging\AppLogger;
use App\Services\Admin\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Controller for admin order management pages.
 *
 * Includes listing, viewing, and managing orders.
 * Uses OrderService for data retrieval and pagination.
 */
class OrdersController extends Controller
{
    public function __construct(
        protected AppLogger $logger,
        private readonly OrderService $orderService,
    ) {
        $this->logger = $logger;
        $this->logger->setContext(context: 'AdminOrdersController Web');
    }

    /**
     * Admin Orders listing page with pagination and search filters.
     */
    public function index(Request $request): Factory|View
    {
        $paginate = $this->orderService->paginate($request);

        $data = OrderResource::collection($paginate->getCollection())
            ->resolve($request);

        $data = collect($data)->map(
            fn (array $order) => json_decode(json_encode($order))
        );

        return view('auth.admin.sections.orders.index', [
            'data' => $data,
            'status_options' => $this->orderService->statusOptions(),
            'filters' => [
                'status' => strtolower(trim((string) $request->query('status', 'all'))),
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
     * Admin view order page by order UID.
     */
    public function show(string $orderUid): Factory|View
    {
        $order = $this->orderService->findByUid($orderUid);

        return view('auth.admin.sections.orders.show', [
            'order' => OrderResource::make($order)->resolve(request()),
        ]);

        // return view('auth.admin.sections.orders.show', [
        //     'orderUid' => $orderUid,
        // ]);
    }
}
