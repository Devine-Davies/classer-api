<?php

namespace App\Console\Commands;

use App\Logging\AppLogger;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;

/**
 * Command to assign a subscription to a user
 *
 * This command allows you to assign a subscription to a user based on * their email and a subscription code.
 *
 * - Examples:
 * - php artisan subscription:activate skywalker@classermedia.com T017A42C
 * - php artisan subscription:activate skywalker@classermedia.com T017A42C 30

 * - php artisan subscription:activate {email} {code} {expiry?}
 */
class SubscriptionActivate extends Command
{
    protected $signature = 'subscription:activate {email} {code} {expiry?}';

    protected $description = 'Activate subscription to a user with mock payment setup';

    public function __construct(
        protected AppLogger $logger,
        protected SubscriptionService $subscriptionService,
    ) {
        $this->logger->setContext(context: 'AssignSubscription');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * - Description
     * This command assigns a subscription to a user based on their email and a subscription code.
     * It creates mock payment method and user subscription records.
     *
     * - Cloud usage
     * If also creates a cloud usage record for the user if it doesn't already exist.
     *
     * - Existing Subscription
     * If the user already has an active subscription, the command will not assign a new one and will throw an error.
     */
    public function handle(): int
    {
        try {
            $email = (string) $this->argument('email');
            $code = (string) $this->argument('code');
            $expiry = (int) ($this->argument('expiry') ?? 120);

            $result = $this->subscriptionService->activateForEmailAndCode($email, $code, $expiry);
            $user = $result['user'];
            $subscription = $result['subscription'];

            // Log success
            $this->logger->info('Assigned subscription successfully', [
                'email' => $email,
                'code' => $code,
                'user_id' => $user->uid,
                'plan_id' => $subscription->uid,
                'date' => now()->toDateTimeString(),
            ]);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            return $this->failed('Failed to assign subscription: '.$e->getMessage());
        }
    }

    /**
     * Handle a command failure.
     */
    public function failed($error): int
    {
        $this->error($error);
        $this->logger->error('AssignSubscription command failed', [
            'email' => $this->argument('email'),
            'code' => $this->argument('code'),
            'error' => $error,
        ]);

        return Command::FAILURE;
    }
}
