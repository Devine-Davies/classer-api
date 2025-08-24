<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Logging\AppLogger;
use App\Enums\AccountStatus;

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

                $user->email = $this->anonymiseEmail($user->email);
                $user->account_status = AccountStatus::DEACTIVATED;
                $user->password = bcrypt(Str::random(32)); // Invalidate password
                $user->save();

                $this->logger->info("User {$user->id} and related data nuked successfully");
                $this->info("✅ User {$user->id} nuked successfully");
            });

            return Command::SUCCESS;
        } catch (\Exception $e) {
            return $this->failed("Failed to nuke user: " . $e->getMessage());
        }
    }

    protected function anonymiseEmail(string $email): string
    {
        // Normalize the email to lowercase first
        $normalizedEmail = strtolower($email);

        // Extract local part and domain
        $originalLocal = strstr($normalizedEmail, '@', true); // before @
        $domain = strstr($normalizedEmail, '@'); // includes @
        $date = now()->format('Ymd');

        // Build predictable anonymised email
        return "deleted-{$date}-{$originalLocal}{$domain}";
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
