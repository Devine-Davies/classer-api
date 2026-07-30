<?php

namespace App\Console\Commands;

use App\Logging\AppLogger;
use App\Models\User;
use App\Services\Admin\UserDeletionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * This command allows you to remove all data related to the user
 *
 * - Examples:
 *   - php artisan assign:nuke-user 614beca2-3e16-4d9c-9427-a7ff95193781
 *   - php artisan assign:nuke-user {id}
 */
class NukeUser extends Command
{
    protected $signature = 'assign:nuke-user {ids} {type}';

    protected $description = 'Nuke one or more users and all their related data';

    public function __construct(protected AppLogger $logger, protected UserDeletionService $userDeletionService)
    {
        $this->logger->setContext('NukeUser');
        parent::__construct();
    }

    public function handle(): int
    {
        // Split by comma, trim whitespace, remove empties
        $ids = array_filter(array_map('trim', explode(',', $this->argument('ids'))));
        $type = (string) $this->argument('type');

        if (empty($ids)) {
            return $this->failed('No valid user IDs provided.');
        }

        if (! in_array($type, ['soft', 'hard'], true)) {
            return $this->failed("Invalid type '{$type}'. Use 'soft' or 'hard'.");
        }

        $hasFailure = false;

        foreach ($ids as $userId) {
            try {
                $user = User::where('id', $userId)
                    ->orWhere('uid', $userId)
                    ->first();

                if (! $user) {
                    $this->error("User not found: {$userId}");
                    $this->logger->warning('User not found', ['id' => $userId]);

                    continue;
                }

                DB::transaction(function () use ($user, $type) {
                    $this->info("Starting nuke process for user: {$user->id}");

                    if ($type === 'soft') {
                        $this->userDeletionService->softDelete($user);
                    } else {
                        $this->userDeletionService->hardDelete($user);
                    }

                    $this->logger->info("User {$user->id} and related data nuked successfully");
                    $this->info("✅ User {$user->id} nuked successfully");
                });
            } catch (\Throwable $e) {
                $hasFailure = true;
                $this->failed("Failed to nuke user {$userId}: ".$e->getMessage());
            }
        }

        return $hasFailure ? Command::FAILURE : Command::SUCCESS;
    }

    protected function failed($error): int
    {
        $this->error($error);
        $this->logger->error('NukeUser command failed', [
            'ids' => $this->argument('ids'),
            'error' => $error,
        ]);

        return Command::FAILURE;
    }
}
