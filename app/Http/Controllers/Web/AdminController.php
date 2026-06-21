<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Logging\AppLogger;
use App\Services\AuthService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

// AuthController

class AdminController extends Controller
{
    public function __construct(
        protected AppLogger $logger,
        protected AuthService $authService
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
        return view('admin.sections.stats.index');
    }

    /**
     * Admin trends page.
     */
    public function trends(): Factory|View
    {
        return view('admin.sections.trends.index');
    }

    /**
     * Admin bulk mails page.
     */
    public function bulkMails(): Factory|View
    {
        return view('admin.sections.bulk-mails.index', [
            'mailTemplates' => config('classer.admin_bulk_mail_templates', []),
        ]);
    }

    /**
     * Admin logs page.
     */
    public function logsIndex(): Factory|View
    {
        $logs = collect(File::files(storage_path('logs')))
            ->filter(fn ($file): bool => $file->getExtension() === 'log')
            ->map(fn ($file): array => [
                'filename' => $file->getFilename(),
                'size' => $file->getSize(),
                'last_modified' => $file->getMTime(),
            ])
            ->sortByDesc('last_modified')
            ->values();

        return view('admin.sections.logs.index', [
            'logs' => $logs,
            'activeLogFile' => $logs->first()['filename'] ?? 'laravel.log',
        ]);
    }
}
