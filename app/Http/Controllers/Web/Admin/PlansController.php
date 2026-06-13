<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanResource;
use App\Logging\AppLogger;
use App\Services\Admin\PlansService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

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

        return view('auth.admin.sections.plans.index', [
            'data' => PlanResource::collection($paginate->items()),
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
    public function store(Request $request): Factory|View
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:120|unique:plans,code',
            'quota' => 'nullable|integer|min:0',
            'type' => 'nullable|string|max:120',
            'duration' => 'nullable|string|max:255',
        ]);

        $plan = $this->plansService->upsert($data);

        // redirect to edit page for the new plan with success message
        return redirect()->route('auth.admin.plans.edit', ['planUid' => $plan->uid])
            ->with('success', 'Plan created successfully. You can now edit the details.');
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
    public function update(Request $request, string $planUid): Factory|View|RedirectResponse
    {
        $entity = $this->plansService->getByUid($planUid);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'quota' => 'nullable|integer|min:0',
            'type' => 'nullable|string|max:120',
            'duration' => 'nullable|string|max:255',
        ]);

        $updated = $this->plansService->upsert(array_merge($data, ['uid' => $planUid]));

        // redirect back to edit page with success message
        return redirect()->route('auth.admin.plans.edit', ['planUid' => $planUid])
            ->with('success', 'Plan updated successfully.');
    }
}
