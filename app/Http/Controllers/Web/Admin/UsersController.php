<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Web\Admin\UserAccountResource;
use App\Logging\AppLogger;
use App\Models\CloudShare;
use App\Services\Admin\UserDeletionService;
use App\Services\Admin\UserService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
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
        private readonly UserDeletionService $userDeletionService,
    ) {
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

        return view('admin.users.index', [
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
        $cloudShareCount = CloudShare::withTrashed()
            ->where('user_id', $user->uid)
            ->count();

        return view('admin.users.show', [
            'user' => (object) UserAccountResource::make($user)->resolve(),
            'cloudShareCount' => $cloudShareCount,
        ]);
    }

    /**
     * Toggle a user's account active/deactivated state from the admin area.
     */
    public function deactivate(string $userId): RedirectResponse
    {
        try {
            $user = $this->userService->findById($userId);
            $this->userDeletionService->toggleAccountStatus($user);

            $this->logger->info('Admin toggled user account status', [
                'user_uid' => $user->uid,
                'user_id' => $user->id,
                'new_status' => $user->account_status->value,
            ]);

            $message = $user->accountDeactivated()
                ? 'User deactivated successfully.'
                : 'User reactivated successfully.';

            return redirect()->back()->with('success', $message);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to toggle user account status', [
                'user_uid' => $userId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to update user account status. Please try again.');
        }
    }

    /**
     * Delete a user from the admin area.
     */
    public function destroy(string $userId): RedirectResponse
    {
        try {
            $user = $this->userService->findById($userId);
            $this->userDeletionService->delete($user, 'hard');

            $this->logger->info('Admin deleted user', [
                'user_uid' => $user->uid,
                'user_id' => $user->id,
            ]);

            return redirect()->route('admin.users')->with('success', 'User deleted successfully.');
        } catch (\Throwable $e) {
            $this->logger->error('Failed to delete user', [
                'user_uid' => $userId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to delete user. Please try again.');
        }
    }
}
