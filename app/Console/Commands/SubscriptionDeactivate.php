<?php

namespace App\Console\Commands;

use App\Logging\AppLogger;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;

/**
 * Command to deactivate (deactivate) a subscription from a user
 *
 * Examples:
 * - php artisan subscription:deactivate {email}
 * - php artisan subscription:deactivate rdd+test@example.com
 */
class SubscriptionDeactivate extends Command
{
    protected $signature = 'subscription:deactivate {email}';

    protected $description = 'Deactivate the active subscription for a given user';

    public function __construct(
        protected AppLogger $logger,
        protected SubscriptionService $subscriptionService,
    ) {
        $this->logger->setContext('SubscriptionDeactivate');
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $email = (string) $this->argument('email');
            $result = $this->subscriptionService->deactivateForEmail($email);
            $user = $result['user'];

            if (! $result['deactivated']) {
                $this->warn("No active subscription found for {$user->email}");
                $this->logger->info('No active subscription found', [
                    'email' => $email,
                    'user_id' => $user->uid,
                ]);

                return Command::SUCCESS;
            }

            $this->info("Unassigned (deactivated) subscription for {$user->email}");
            $this->logger->info('Unassigned subscription successfully', [
                'email' => $email,
                'user_id' => $user->uid,
                'plan_id' => $result['plan_id'],
                'date' => now()->toDateTimeString(),
            ]);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            return $this->failed('Failed to unassign subscription: '.$e->getMessage());
        }
    }

    protected function failed(string $error): int
    {
        $this->error($error);
        $this->logger->error('UnassignSubscription command failed', [
            'email' => $this->argument('email'),
            'error' => $error,
        ]);

        return Command::FAILURE;
    }
}
