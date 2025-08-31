<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Subscription;
use App\Models\PaymentMethod;
use App\Models\UserSubscription;
use App\Models\UserCloudUsage;
use App\Logging\AppLogger;
use App\Jobs\MailUserSubscriptionActivated;

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

    public function __construct(protected AppLogger $logger)
    {
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
     *
     * @return int
     */
    public function handle(): int
    {
        try {
            $email = $this->argument('email');
            $code  = $this->argument('code');
            $expiry = $this->argument('expiry') ?? 120; // Default to 120 days if not provided

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException("Invalid email format: {$email}");
            }

            if (empty($code)) {
                throw new \InvalidArgumentException("Subscription code cannot be empty.");
            }

            /* @var User $user */
            $user = User::where('email', $email)->first();
            if (! $user) {
                throw new \InvalidArgumentException("User with email '{$email}' not found.");
            }

            $subscription = Subscription::where('code', $code)->first();
            if (! $subscription) {
                throw new \InvalidArgumentException("Subscription with code '{$code}' not found.");
            }

            // Check if the user already has an active subscription
            if ($user->subscription && $user->subscription->status === 'active') {
                throw new \Exception("User with email '{$email}' already has an active subscription.");
            }

            DB::transaction(function () use ($user, $subscription, $expiry) {
                $paymentMethod = PaymentMethod::create([
                    'uid' => Str::uuid(),
                    'user_id' => $user->uid,
                    'provider' => 'stripe',
                    'type' => 'service',
                    'stripe_customer_id' => 'cus_' . Str::random(16),
                    'stripe_payment_method_id' => 'pm_' . Str::random(16),
                    'stripe_transaction_id' => 'tr_' . Str::random(16),
                    'created_at' => now()->subDays(30),
                    'updated_at' => now()->subDays(30),
                ]);

                UserSubscription::create([
                    'uid' => Str::uuid(),
                    'user_id' => $user->uid,
                    'subscription_id' => $subscription->uid,
                    'payment_method_id' => $paymentMethod->uid,
                    'status' => 'active',
                    'expiration_date' => now()->addDays(intval($expiry)),
                    'auto_renew_date' => now()->addMonths(6),
                    'auto_renew' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // only create cloud usage if it doesn't exist
                if (!UserCloudUsage::where('user_id', $user->uid)->exists()) {
                    UserCloudUsage::create([
                        'uid' => Str::uuid(),
                        'user_id' => $user->uid,
                        'total_usage' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

            // Send subscription activated email
            MailUserSubscriptionActivated::dispatch($user, $subscription);

            // Log success
            $this->logger->info("Assigned subscription successfully", [
                'email' => $email,
                'code' => $code,
                'user_id' => $user->uid,
                'subscription_id' => $subscription->uid,
                'date' => now()->toDateTimeString(),
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            return $this->failed("Failed to assign subscription: " . $e->getMessage());
        }
    }

    /**
     * Handle a command failure.
     */
    function failed($error): int
    {
        $this->error($error);
        $this->logger->error("AssignSubscription command failed", [
            'email' => $this->argument('email'),
            'code' => $this->argument('code'),
            'error' => $error,
        ]);

        return Command::FAILURE;
    }
}
