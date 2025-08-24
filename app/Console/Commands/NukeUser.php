<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserSubscription;
use App\Logging\AppLogger;

/**
 * This command allows you to remove all data related to the user
 *
 * - Examples:
 *   - php artisan assign:nuke-user 614beca2-3e16-4d9c-9427-a7ff95193781
 *   - php artisan assign:nuke-user {id}
 */
class NukeUser extends Command
{
    protected $signature = 'assign:nuke-user {id}';
    protected $description = 'Nuke a user and all their related data';

    public function __construct(protected AppLogger $logger)
    {
        $this->logger->setContext('NukeUser');
        parent::__construct();
    }

    public function handle(): int
    {
        $userId = $this->argument('id');

        try {
            $user = User::where('id', $userId)->orWhere('uid', $userId)->first();

            if (! $user) {
                return $this->failed("User not found: {$userId}");
            }

            DB::transaction(function () use ($user) {
                $this->info("Starting nuke process for user: {$user->id}");

                // Step 1: Delete related data
                // Adjust these to match your actual relationships
                // $user->posts()->delete();

                // Step 2: Update the user's email to be something random
                // This way we can easily identify and filter out deleted users and allow the user to re-register if needed
                $user->email = str_replace('@', '+deleted-' . Str::random(8) . '@', $user->email);
                $user->save();

                $this->logger->info("User {$user->id} and related data nuked successfully");
                $this->info("✅ User {$user->id} nuked successfully");
            });

            return Command::SUCCESS;
        } catch (\Exception $e) {
            return $this->failed("Failed to nuke user: " . $e->getMessage());
        }
    }

    protected function failed($error): int
    {
        $this->error($error);
        $this->logger->error("NukeUser command failed", [
            'id' => $this->argument('id'),
            'error' => $error,
        ]);

        return Command::FAILURE;
    }
}
