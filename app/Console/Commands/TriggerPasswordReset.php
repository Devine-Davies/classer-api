<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Logging\AppLogger;
use App\Utils\PasswordRestToken;
use App\Jobs\MailUserPasswordReset;

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
            return $this->failed("No valid user IDs provided.");
        }

        foreach ($ids as $userId) {
            $user = User::where('uid', $userId)->first();

            if (! $user) {
                $this->logger->warning("User not found", ['id' => $userId]);
                continue;
            }

            try {
                DB::transaction(function () use ($user) {
                    $passwordResetToken = new PasswordRestToken();
                    $user->password = bcrypt(Str::random(32)); // Invalidate current password
                    $user->password_reset_token = $passwordResetToken->generateToken();
                    $user->save();

                    MailUserPasswordReset::dispatch($user);
                    $this->logger->info("Password reset cmd triggered", ['user_id' => $user->id]);
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
