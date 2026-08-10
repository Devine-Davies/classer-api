<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\AccountStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmailBroadcastController extends Controller
{
    public function index(): Factory|View
    {
        return view('admin.email-broadcasts.index', [
            'broadcastTemplates' => config('classer.admin_bulk_mail_templates', []),
        ]);
    }

    public function queue(Request $request): RedirectResponse
    {
        $templateDefinitions = collect(config('classer.admin_bulk_mail_templates', []));
        $templateKeys = $templateDefinitions->keys()->values()->all();

        if (empty($templateKeys)) {
            return redirect()
                ->route('admin.email-broadcasts')
                ->withInput()
                ->with('error', 'No admin email broadcast templates are configured.');
        }

        $raw = (string) $request->input('emails', '');
        $template = (string) $request->input('template', '');
        $emails = collect(preg_split('/[\s,]+/', $raw))
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->filter()
            ->unique()
            ->values();

        $validator = Validator::make(
            [
                'template' => $template,
                'emails' => $emails->toArray(),
            ],
            [
                'template' => ['required', 'string', 'in:'.implode(',', $templateKeys)],
                'emails' => ['required', 'array', 'min:1'],
                'emails.*' => ['email'],
            ],
        );

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $selectedTemplate = $templateDefinitions->get($template);

        if (! is_array($selectedTemplate) || empty($selectedTemplate['job'])) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Selected template is not configured correctly.');
        }

        $jobClass = $selectedTemplate['job'];

        if (! class_exists($jobClass)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Selected template job does not exist.');
        }

        $allowedStatuses = collect($selectedTemplate['account_statuses'] ?? [])
            ->map(fn ($status) => (int) $status)
            ->values()
            ->all();

        $matchedUsers = User::query()
            ->whereIn('email', $emails)
            ->get();

        $users = collect($matchedUsers);

        if (! empty($allowedStatuses)) {
            $users = $users->filter(function (User $user) use ($allowedStatuses) {
                $status = $user->account_status;
                $statusValue = $status instanceof AccountStatus ? $status->value : (int) $status;

                return in_array($statusValue, $allowedStatuses, true);
            })->values();
        }

        $foundEmails = $matchedUsers->pluck('email')->map(fn ($email) => strtolower((string) $email))->values();
        $eligibleEmails = $users->pluck('email')->map(fn ($email) => strtolower((string) $email))->values();
        $ineligible = $foundEmails->diff($eligibleEmails)->values()->all();
        $notFound = $emails->diff($foundEmails)->values()->all();

        $users->chunk(200)->each(function ($chunk) use ($jobClass) {
            foreach ($chunk as $user) {
                $jobClass::dispatch($user);
            }
        });

        return redirect()
            ->route('admin.email-broadcasts')
            ->with('success', 'Queued '.$users->count().' emails using "'.($selectedTemplate['label'] ?? $template).'".')
            ->with('emailBroadcastResult', [
                'total_sent' => $users->count(),
                'sent' => $eligibleEmails->all(),
                'not_found' => $notFound,
                'ineligible' => $ineligible,
                'template' => [
                    'key' => $template,
                    'label' => $selectedTemplate['label'] ?? $template,
                ],
            ]);
    }
}