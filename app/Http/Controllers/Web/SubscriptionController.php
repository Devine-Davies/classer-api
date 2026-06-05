<?php

namespace App\Http\Controllers\Web;

use App\Logging\AppLogger;
use App\Models\UserSubscription;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class SubscriptionController extends Controller
{
    public function __construct(protected AppLogger $logger)
    {
        $this->logger = $logger;
        $this->logger->setContext(context: 'AuthController');
    }

    /**
     * Show the application subscriptions page.
     */
    public function subscriptions()
    {
        // Load subscription data
        $subscriptions = app(SubscriptionService::class)->loadMergedSubscriptions();

        // subscriptions/index
        return view('subscriptions/index/index', [
            'user' => null,
            'openApp' => null,
            'subscription' => null,
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * Show the application subscriptions page.
     */
    public function subscriptionsUser(Request $request, string $token)
    {
        $accessToken = PersonalAccessToken::findToken($token);
        $user = $accessToken?->tokenable;

        if (! $user) {
            return redirect('/subscriptions')
                ->with('error', 'Invalid or expired token. Please try again.');
        }

        $subscriptions = app(SubscriptionService::class)->loadMergedSubscriptions();
        $selectedPlan = null;
        $payload = $request->session()->get('payload');
        if ($user && is_array($payload)) {
            $planCode = $payload['code'] ?? null;

            if (! $planCode) {
                return Log::warning('Missing plan in payload', ['payload' => $payload]);
            }

            $selectedPlan = $subscriptions->firstWhere('code', $planCode);
            if (! $selectedPlan) {
                return Log::warning('Invalid plan code in payload', ['code' => $planCode]);
            }

            if (! $selectedPlan['subscription_id']) {
                return Log::warning('Missing subscription_id for plan', ['code' => $selectedPlan]);
            }

            $this->createSeededSubscription($user, $selectedPlan['subscription_id']);
        }

        return view('subscriptions', [
            'user' => $user,
            'openApp' => $accessToken ? 'classer://auth/login?token='.$token : null,
            'subscription' => $selectedPlan,
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     *  Handle the selection of a subscription plan.
     */
    public function handleRedirect(Request $request)
    {
        return redirect()->back()->with('payload', [
            'code' => $request->input('code'),
            'timestamp' => time(),
        ]);
    }

    /**
     * Create a seeded subscription for testing purposes.
     */
    protected function createSeededSubscription($user, $subscriptionId): void
    {
        try {
            DB::transaction(function () use ($user, $subscriptionId) {
                UserSubscription::create([
                    'uid' => Str::uuid(),
                    'user_id' => $user->uid,
                    'subscription_id' => $subscriptionId,
                    'status' => 'active',
                    'auto_renew' => true,
                    'expiration_date' => now()->addMonths(6),
                    'auto_renew_date' => now()->addMonths(6),
                    'transaction_id' => 'pi_'.Str::random(16),
                    'updated_by' => 'system',
                    'notes' => 'Seeded subscription for testing',
                ]);
            });
        } catch (\Exception $e) {
            $this->logger->error('Failed to create seeded subscription', [
                'user_id' => $user->uid,
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
