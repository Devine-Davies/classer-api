<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Logging\AppLogger;
use App\Enums\AccountStatus;
use App\Models\UserSubscription;

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

    public function __construct(protected AppLogger $logger)
    {
        $this->logger->setContext('NukeUser');
        parent::__construct();
    }

    public function handle(): int
    {
        // Split by comma, trim whitespace, remove empties
        $ids = array_filter(array_map('trim', explode(',', $this->argument('ids'))));
        $type = $this->argument('type');

        if (empty($ids)) {
            return $this->failed("No valid user IDs provided.");
        }

        foreach ($ids as $userId) {
            try {
                $user = User::where('id', $userId)
                    ->orWhere('uid', $userId)
                    ->first();

                if (! $user) {
                    $this->error("User not found: {$userId}");
                    $this->logger->warning("User not found", ['id' => $userId]);
                    continue;
                }

                DB::transaction(function () use ($user, $type) {
                    $this->info("Starting nuke process for user: {$user->id}");

                    if ($type === 'soft') {
                        $user->email = $this->anonymiseEmail($user->email);
                        $user->account_status = AccountStatus::DEACTIVATED;
                        $user->password = bcrypt(Str::random(32)); // Invalidate password
                        $user->save();
                    } elseif ($type === 'hard') {
                        // UserSubscription::where('user_id', $user->id)->forceDelete();
                        $user->forceDelete();
                    }

                    $this->logger->info("User {$user->id} and related data nuked successfully");
                    $this->info("✅ User {$user->id} nuked successfully");
                });
            } catch (\Exception $e) {
                $this->failed("Failed to nuke user {$userId}: " . $e->getMessage());
            }
        }

        return Command::SUCCESS;
    }

    protected function anonymiseEmail(string $email): string
    {
        $normalizedEmail = strtolower($email);
        $originalLocal = strstr($normalizedEmail, '@', true);
        $domain = strstr($normalizedEmail, '@');
        $date = now()->format('Ymd');

        return "deleted-{$date}-{$originalLocal}{$domain}";
    }

    protected function failed($error): int
    {
        $this->error($error);
        $this->logger->error("NukeUser command failed", [
            'ids' => $this->argument('ids'),
            'error' => $error,
        ]);

        return Command::FAILURE;
    }
}
