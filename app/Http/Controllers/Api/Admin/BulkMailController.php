<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\AccountStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class BulkMailController extends Controller
{
    /**
     * Queue bulk emails for the selected template.
     */
    public function queue(Request $request): JsonResponse
    {
        $templateDefinitions = collect(config('classer.admin_bulk_mail_templates', []));
        $templateKeys = $templateDefinitions->keys()->values()->all();

        if (empty($templateKeys)) {
            return response()->json(
                [
                    'status' => false,
                    'errors' => ['No admin bulk mail templates are configured.'],
                ],
                422,
            );
        }

        $raw = (string) $request->input('emails', '');
        $template = (string) $request->input('template', '');
        $emails = collect(preg_split('/[\s,]+/', $raw))
            ->map(fn($email) => strtolower(trim($email)))
            ->filter()
            ->unique()
            ->values();

        $validator = Validator::make(
            [
                'template' => $template,
                'emails' => $emails->toArray(),
            ],
            [
                'template' => ['required', 'string', 'in:' . implode(',', $templateKeys)],
                'emails' => ['required', 'array', 'min:1'],
                'emails.*' => ['email'],
            ],
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    'status' => false,
                    'errors' => $validator->errors()->all(),
                ],
                422,
            );
        }

        $selectedTemplate = $templateDefinitions->get($template);
        if (!is_array($selectedTemplate) || empty($selectedTemplate['job'])) {
            return response()->json(
                [
                    'status' => false,
                    'errors' => ['Selected template is not configured correctly.'],
                ],
                422,
            );
        }

        $jobClass = $selectedTemplate['job'];
        if (!class_exists($jobClass)) {
            return response()->json(
                [
                    'status' => false,
                    'errors' => ['Selected template job does not exist.'],
                ],
                422,
            );
        }

        $allowedStatuses = collect($selectedTemplate['account_statuses'] ?? [])
            ->map(fn($status) => (int) $status)
            ->values()
            ->all();

        $matchedUsers = User::query()
            ->whereIn('email', $emails)
            ->get();

        $users = collect($matchedUsers);
        if (!empty($allowedStatuses)) {
            $users = $users->filter(function (User $user) use ($allowedStatuses) {
                $status = $user->account_status;
                $statusValue = $status instanceof AccountStatus ? $status->value : (int) $status;
                return in_array($statusValue, $allowedStatuses, true);
            })->values();
        }

        $foundEmails = $matchedUsers->pluck('email')->map(fn($email) => strtolower($email))->values();
        $eligibleEmails = $users->pluck('email')->map(fn($email) => strtolower($email))->values();
        $ineligible = $foundEmails->diff($eligibleEmails)->values();
        $notFound = $emails->diff($foundEmails)->values();

        $users->chunk(200)->each(function ($chunk) use ($jobClass) {
            foreach ($chunk as $user) {
                $jobClass::dispatch($user);
            }
        });

        return response()->json([
            'status' => true,
            'message' => 'Emails are being queued',
            'data' => [
                'total_sent' => $users->count(),
                'sent' => $eligibleEmails,
                'not_found' => $notFound,
                'ineligible' => $ineligible,
                'template' => [
                    'key' => $template,
                    'label' => $selectedTemplate['label'] ?? $template,
                ],
            ],
        ]);
    }
}