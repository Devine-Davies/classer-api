<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Logging\AppLogger;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AdminController extends Controller
{
    public function __construct(
        protected AppLogger $logger
    ) {
        $this->logger = $logger;
        $this->logger->setContext(context: 'AdminController Web');
    }

    /**
     * Admin section root.
     */
    public function admin(): RedirectResponse
    {
        return redirect('/auth/admin/users');
    }

    /**
     * Admin stats page.
     */
    public function stats(): Factory|View
    {
        return view('auth.admin.sections.stats.index');
    }

    /**
     * Admin trends page.
     */
    public function trends(): Factory|View
    {
        return view('auth.admin.sections.trends.index');
    }

    /**
     * Admin bulk mails page.
     */
    public function bulkMails(): Factory|View
    {
        return view('auth.admin.sections.bulk-mails.index', [
            'mailTemplates' => config('classer.admin_bulk_mail_templates', []),
        ]);
    }

    /**
     * Admin logs page.
     */
    public function logs(): Factory|View
    {
        return view('auth.admin.sections.logs.index');
    }
}
