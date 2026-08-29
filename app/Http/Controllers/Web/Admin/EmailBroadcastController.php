<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\AccountStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Utils\PasswordResetToken;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmailBroadcastController extends Controller
{
    public function index(Request $request): Factory|View
    {
        $broadcastTemplates = config('classer.admin_bulk_mail_templates', []);
        $selectedTemplate = trim((string) $request->query('template', ''));

        return view('admin.email-broadcasts.index', [
            'broadcastTemplates' => $broadcastTemplates,
            'prefilledTemplate' => array_key_exists($selectedTemplate, $broadcastTemplates)
                ? $selectedTemplate
                : '',
            'prefilledEmails' => trim((string) $request->query('emails', '')),
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
        $emails = $this->parseEmails($raw);

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
        $preparePasswordReset = (bool) ($selectedTemplate['prepare_password_reset'] ?? false);

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

        [$matchedUsers, $users] = $this->resolveRecipients($emails, $allowedStatuses);

        $foundEmails = $matchedUsers->pluck('email')->map(fn ($email) => strtolower((string) $email))->values();
        $eligibleEmails = $users->pluck('email')->map(fn ($email) => strtolower((string) $email))->values();
        $ineligible = $foundEmails->diff($eligibleEmails)->values()->all();
        $notFound = $emails->diff($foundEmails)->values()->all();

        $users->chunk(200)->each(function ($chunk) use ($jobClass, $preparePasswordReset) {
            foreach ($chunk as $user) {
                if (! $preparePasswordReset) {
                    $jobClass::dispatch($user);

                    continue;
                }

                DB::transaction(function () use ($jobClass, $user): void {
                    $user->password_reset_token = (new PasswordResetToken)->generateToken();
                    $user->save();
                    $jobClass::dispatch($user);
                });
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

    public function preview(Request $request): JsonResponse
    {
        $templateDefinitions = collect(config('classer.admin_bulk_mail_templates', []));
        $emails = $this->parseEmails((string) $request->input('emails', ''));
        $template = (string) $request->input('template', '');

        $validator = Validator::make(
            ['template' => $template, 'emails' => $emails->toArray()],
            [
                'template' => ['required', 'string', 'in:'.$templateDefinitions->keys()->implode(',')],
                'emails' => ['required', 'array', 'min:1'],
                'emails.*' => ['email'],
            ],
        );

        if ($validator->fails()) {
            return response()->json(['message' => 'Enter a valid template and recipient list.'], 422);
        }

        $selectedTemplate = $templateDefinitions->get($template, []);
        $allowedStatuses = collect($selectedTemplate['account_statuses'] ?? [])
            ->map(fn ($status) => (int) $status)
            ->values()
            ->all();
        [$matchedUsers, $eligibleUsers] = $this->resolveRecipients($emails, $allowedStatuses);

        return response()->json([
            'recipients' => $emails->count(),
            'eligible' => $eligibleUsers->count(),
            'ineligible' => $matchedUsers->count() - $eligibleUsers->count(),
            'not_found' => $emails->count() - $matchedUsers->count(),
        ]);
    }

    private function parseEmails(string $raw): Collection
    {
        return collect(preg_split('/[\s,]+/', $raw))
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->filter()
            ->unique()
            ->values();
    }

    private function resolveRecipients(Collection $emails, array $allowedStatuses): array
    {
        $matchedUsers = User::query()->whereIn('email', $emails)->get();
        $eligibleUsers = $matchedUsers;

        if (! empty($allowedStatuses)) {
            $eligibleUsers = $matchedUsers->filter(function (User $user) use ($allowedStatuses) {
                $status = $user->account_status;
                $statusValue = $status instanceof AccountStatus ? $status->value : (int) $status;

                return in_array($statusValue, $allowedStatuses, true);
            })->values();
        }

        return [$matchedUsers, $eligibleUsers];
    }
}
