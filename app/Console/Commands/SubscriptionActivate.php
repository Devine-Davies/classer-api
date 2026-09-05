<?php

namespace App\Console\Commands;

use App\Logging\AppLogger;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;

/**
 * Assign a plan subscription to a user.
 */
class SubscriptionActivate extends Command
{
    protected $signature = 'subscription:activate
                                                        {email : Email address of the user}
                                                        {code : Subscription plan code}
                                                        {expiry? : Optional expiry in days (defaults to 120)}';

    protected $description = 'Activate subscription to a user with mock payment setup';

    protected $help = <<<'HELP'
                Activate a plan for 120 days:
                    php artisan subscription:activate user@example.com FBNG9TZB

                Activate a plan for 30 days:
                    php artisan subscription:activate user@example.com FBNG9TZB 30

                The selected plan determines which capabilities, such as Cloud Share and
                Cloud Backup, are granted to the user.
                HELP;

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
            $subscription->loadMissing('plan.entitlements');

            $capabilities = $subscription->plan?->entitlements
                ->pluck('capability')
                ->sort()
                ->values()
                ->implode(', ');

            $this->info(sprintf(
                'Activated %s for %s. Capabilities: %s',
                $subscription->plan?->title ?? $code,
                $user->email,
                $capabilities !== '' ? $capabilities : 'none',
            ));

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
