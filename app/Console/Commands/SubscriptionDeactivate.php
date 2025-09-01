<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserSubscription;
use App\Logging\AppLogger;
use Illuminate\Support\Facades\DB;

/**
 * Command to deactivate (deactivate) a subscription from a user
 *
 * Examples:
 * - php artisan subscription:deactivate {email}
 * - php artisan subscription:deactivate rdd+test@example.com
 * 
 */
class SubscriptionDeactivate extends Command
{
    protected $signature = 'subscription:deactivate {email}';
    protected $description = 'Deactivate the active subscription for a given user';

    public function __construct(protected AppLogger $logger)
    {
        $this->logger->setContext('SubscriptionDeactivate');
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $email = $this->argument('email');

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException("Invalid email format: {$email}");
            }

            /** @var User $user */
            $user = User::where('email', $email)->first();
            if (! $user) {
                throw new \InvalidArgumentException("User with email '{$email}' not found.");
            }

            // Check if user has active subscription
            $activeSub = UserSubscription::where('user_id', $user->uid)
                ->where('status', 'active')
                ->first();

            if (! $activeSub) {
                $this->warn("No active subscription found for {$user->email}");
                $this->logger->info("No active subscription found", [
                    'email' => $email,
                    'user_id' => $user->uid,
                ]);
                return Command::SUCCESS;
            }

            DB::transaction(function () use ($activeSub) {
                $activeSub->update([
                    'status' => 'inactive',
                    'updated_at' => now(),
                ]);
            });

            $this->info("Unassigned (deactivated) subscription for {$user->email}");
            $this->logger->info("Unassigned subscription successfully", [
                'email' => $email,
                'user_id' => $user->uid,
                'subscription_id' => $activeSub->subscription_id,
                'date' => now()->toDateTimeString(),
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            return $this->failed("Failed to unassign subscription: " . $e->getMessage());
        }
    }

    protected function failed(string $error): int
    {
        $this->error($error);
        $this->logger->error("UnassignSubscription command failed", [
            'email' => $this->argument('email'),
            'error' => $error,
        ]);

        return Command::FAILURE;
    }
}
