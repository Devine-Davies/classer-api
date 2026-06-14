<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanResource;
use App\Logging\AppLogger;
use App\Services\Admin\PlansService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Controller for admin management pages.
 *
 * Includes listing, adding, and editing plans.
 * Uses PlanTableService for data retrieval and pagination.
 */
class PlansController extends Controller
{
    public function __construct(
        protected AppLogger $logger,
        private readonly PlansService $plansService,
    ) {
        $this->logger = $logger;
        $this->logger->setContext(context: 'PlansController Web');
    }

    /**
     * Admin Plans listing page with pagination and search filters.
     */
    public function index(Request $request): Factory|View
    {
        $paginate = $this->plansService->paginate($request);
        $data = collect(
            PlanResource::collection($paginate->items())->resolve($request)
        )->map(function (array $plan) {
            return json_decode(json_encode($plan));
        });

        return view('auth.admin.sections.plans.index', [
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
     * Admin add subscription page.
     */
    public function add(): Factory|View
    {
        return view('auth.admin.sections.plans.add');
    }

    /**
     * Handle store plan form submission.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:120|unique:plans,code',
            'quota' => 'nullable|integer|min:0',
            'type' => 'nullable|string|max:120',
            'duration' => 'nullable|string|max:255',
        ]);

        $plan = $this->plansService->create($data);
        $didCreate = $plan !== null;
        $withMessage = $didCreate ? ['success' => 'Plan created successfully.'] : ['error' => 'Failed to create the plan. Please try again.'];

        // redirect to edit page for the new plan with success message
        return redirect()->route('auth.admin.plans.edit', ['planUid' => $plan->uid])
            ->with($withMessage);
    }

    /**
     * Admin edit subscription page by plan UID.
     */
    public function edit(string $planUid): Factory|View
    {
        $entity = $this->plansService->getByUid($planUid);

        return view('auth.admin.sections.plans.edit', [
            'entity' => PlanResource::make($entity),
        ]);
    }

    /**
     * Handle update plan form submission.
     */
    public function update(Request $request, string $planUid): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'quota' => 'nullable|integer|min:0',
            'type' => 'nullable|string|max:120',
            'duration' => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
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

        $updated = $this->plansService->update($planUid, $data);
        $withMessage = $updated ? ['success' => 'Plan updated successfully.'] : ['error' => 'Failed to update the plan. Please try again.'];

        // redirect back to edit page with success or error message
        return redirect()->route('auth.admin.plans.edit', ['planUid' => $planUid])
            ->with($withMessage);
    }
}
