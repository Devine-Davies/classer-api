<?php

namespace App\Console\Commands;

use App\Jobs\MailUserPasswordReset;
use App\Logging\AppLogger;
use App\Models\User;
use App\Utils\PasswordResetToken;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * This command triggers a password reset for one or more users.
 *
 * - Examples:
 *   - php artisan trigger-password-reset 614beca2-3e16-4d9c-9427-a7ff95193781
 *   - php artisan trigger-password-reset {id}
 */
class TriggerPasswordReset extends Command
{
    protected $signature = 'trigger-password-reset {ids}';

    protected $description = 'Trigger a password reset for one or more users';

    public function __construct(protected AppLogger $logger)
    {
        $this->logger->setContext('TriggerPasswordReset');
        parent::__construct();
    }

    public function handle(): int
    {
        // Split by comma, trim whitespace, remove empties
        $ids = array_filter(array_map('trim', explode(',', $this->argument('ids'))));

        if (empty($ids)) {
            return $this->failed('No valid user IDs provided.');
        }

        $hasFailure = false;

        foreach ($ids as $userId) {
            $user = User::where('uid', $userId)->first();

            if (! $user) {
                $this->error("User not found: {$userId}");
                $this->logger->warning('User not found', ['id' => $userId]);

                continue;
            }

            try {
                DB::transaction(function () use ($user) {
                    $passwordResetToken = new PasswordResetToken;
                    $user->password = bcrypt(Str::random(32)); // Invalidate current password
                    $user->password_reset_token = $passwordResetToken->generateToken();
                    $user->save();

                    MailUserPasswordReset::dispatch($user);
                    $this->logger->info('Password reset cmd triggered', ['user_id' => $user->id]);
                });

                $this->info("Password reset triggered for {$user->uid}");
            } catch (\Throwable $e) {
                $hasFailure = true;
                $this->failed("Failed to trigger password reset for user {$userId}: ".$e->getMessage());
            }
        }

        return $hasFailure ? Command::FAILURE : Command::SUCCESS;
    }

    protected function failed($error): int
    {
        $this->error($error);
        $this->logger->error('TriggerPasswordReset command failed', [
            'ids' => $this->argument('ids'),
            'error' => $error,
        ]);

        return Command::FAILURE;
    }
}
