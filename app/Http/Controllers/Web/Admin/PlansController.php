<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Admin\PlanCreateRequest;
use App\Http\Requests\Web\Admin\PlanUpdateRequest;
use App\Http\Resources\Web\Admin\PlanResource;
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

        return view('admin.sections.plans.index', [
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
        return view('admin.sections.plans.add');
    }

    /**
     * Handle store plan form submission.
     */
    public function store(PlanCreateRequest $request): RedirectResponse
    {
        $plan = $this->plansService->create(
            data: $request->planPayload()
        );

        $withMessage = $plan
            ? ['success' => 'Plan has been created successfully and a catalog item has been created for it. You can now edit these details.']
            : ['error' => 'Failed to create the plan. Please try again.'];

        return redirect()->route('admin.plans.edit', ['planUid' => $plan->uid])
            ->with($withMessage);
    }

    /**
     * Admin edit subscription page by plan UID.
     */
    public function edit(string $planUid): Factory|View
    {
        $entity = $this->plansService->getByUid($planUid);

        return view('admin.sections.plans.edit', [
            'entity' => PlanResource::make($entity),
        ]);
    }

    /**
     * Handle update plan form submission.
     */
    public function update(PlanUpdateRequest $request, string $planUid): RedirectResponse
    {
        $updated = $this->plansService->update(
            $planUid,
            $request->planPayload()
        );

        $withMessage = $updated
            ? ['success' => 'Updated successfully.']
            : ['error' => 'Failed to update the plan. Please try again.'];

        return redirect()
            ->route('admin.plans.edit', ['planUid' => $planUid])
            ->with($withMessage);
    }
}
