<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Web\Admin\UserAccountResource;
use App\Logging\AppLogger;
use App\Services\Admin\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Controller for admin user management pages.
 *
 * Includes listing, adding, and editing users.
 * Uses UserService for data retrieval and pagination.
 */
class UsersController extends Controller
{
    public function __construct(
        protected AppLogger $logger,
        private readonly UserService $userService,
    ) {
        $this->logger = $logger;
        $this->logger->setContext(context: 'AdminUsersController Web');
    }

    /**
     * Admin Users listing page with pagination and search filters.
     */
    public function index(Request $request): Factory|View
    {
        $paginate = $this->userService->paginate($request);
        $users = collect(
            UserAccountResource::collection($paginate->items())->resolve($request)
        )->map(function (array $user) {
            return json_decode(json_encode($user));
        });

        return view('admin.sections.users.index', [
            'users' => $users,
            'filters' => [
                'has_subscription' => strtolower(trim((string) $request->query('has_subscription', 'all'))),
                'account_state' => strtolower(trim((string) $request->query('account_state', 'all'))),
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
     * Admin user details page.
     */
    public function show(string $userId): Factory|View
    {
        $user = $this->userService->findById($userId);
        return view('admin.sections.users.show', [
            'user' => (object) UserAccountResource::make($user)->resolve(),
        ]);
    }
}
