<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Logging\AppLogger;
use App\Services\Admin\LogsService;
use App\Services\Admin\TrendsService;
use App\Services\AuthService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// AuthController

class AdminController extends Controller
{
    public function __construct(
        protected AppLogger $logger,
        protected AuthService $authService,
        private readonly LogsService $logsService,
        private readonly TrendsService $trendsService,
    ) {
        $this->logger = $logger;
        $this->logger->setContext(context: 'AdminController Web');
    }

    /**
     * Show admin login page.
     */
    public function showLogin(): Factory|View
    {
        return view('admin.login.index');
    }

    /**
     * Admin login page.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $result = $this->authService->authenticate(
            email: $credentials['email'],
            password: $credentials['password'],
            abilities: ['admin'],
            createToken: false,
            recordLogin: true
        );

        if (! $result['status']) {
            return back()
                ->withErrors([
                    'email' => $result['message'],
                ])
                ->onlyInput('email');
        }

        if (! $this->authService->isAdmin($result['user'])) {
            return back()
                ->withErrors([
                    'email' => 'This account is not allowed to access the admin area.',
                ])
                ->onlyInput('email');
        }

        $this->authService->loginWebUser($result['user']);

        $request->session()->regenerate();

        session([
            'api_token' => Auth::user()->createToken('admin')->plainTextToken,
        ]);

        return redirect()
            ->route('admin.stats')
            ->with('success', 'You have been logged in successfully.');
    }

    /**
     * Admin logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logoutWebUser();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login')
            ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Admin stats page.
     */
    public function stats(): Factory|View
    {
        return view('admin.stats.index');
    }

    /**
     * Admin trends page.
     */
    public function trends(Request $request): Factory|View
    {
        $payload = $this->trendsService->build($request);

        return view('admin.trends.index', $payload);
    }

    /**
     * Admin bulk mails page.
     */
    public function bulkMails(): Factory|View
    {
        return view('admin.bulk-mails.index', [
            'mailTemplates' => config('classer.admin_bulk_mail_templates', []),
        ]);
    }

    /**
     * Admin logs page.
     */
    public function logs(Request $request): Factory|View
    {
        $logs = $this->logsService->availableFiles();
        $activeLogFile = $this->logsService->resolveActiveFilename($request, $logs);

        $paginatedRows = null;

        if ($activeLogFile !== null) {
            $paginatedRows = $this->logsService->paginateRows($request, $activeLogFile);
        }

        return view('admin.logs.index', [
            'logs' => $logs,
            'activeLogFile' => $activeLogFile,
            'rows' => collect($paginatedRows?->items() ?? [])->map(
                fn (array $row) => (object) $row
            ),
            'filters' => [
                'q' => trim((string) $request->query('q', '')),
                'limit' => max(10, min((int) $request->query('limit', 50), 200)),
            ],
            'pagination' => [
                'total' => $paginatedRows?->total() ?? 0,
                'per_page' => $paginatedRows?->perPage() ?? 0,
                'current_page' => $paginatedRows?->currentPage() ?? 1,
                'last_page' => $paginatedRows?->lastPage() ?? 1,
                'from' => $paginatedRows?->firstItem() ?? 0,
                'to' => $paginatedRows?->lastItem() ?? 0,
            ],
        ]);
    }

    /**
     * Clear a selected log file and redirect back to logs page.
     */
    public function clearLog(Request $request): RedirectResponse
    {
        $requestedFile = trim((string) $request->input('file', ''));
        $confirmFile = trim((string) $request->input('confirm_file', ''));
        $q = trim((string) $request->input('q', ''));
        $limit = max(10, min((int) $request->input('limit', 50), 200));

        $query = array_filter([
            'file' => $requestedFile !== '' ? $requestedFile : null,
            'q' => $q !== '' ? $q : null,
            'limit' => $limit !== 50 ? $limit : null,
        ]);

        if ($requestedFile === '') {
            return redirect()
                ->route('admin.logs', $query)
                ->with('error', 'No log file selected.');
        }

        if ($confirmFile !== $requestedFile) {
            return redirect()
                ->route('admin.logs', $query)
                ->with('error', 'Confirmation text does not match the selected log file.');
        }

        $cleared = $this->logsService->clearFile($requestedFile);

        if (! $cleared) {
            return redirect()
                ->route('admin.logs', $query)
                ->with('error', 'Unable to clear that log file.');
        }

        return redirect()
            ->route('admin.logs', $query)
            ->with('success', 'Log file cleared successfully.');
    }
}
