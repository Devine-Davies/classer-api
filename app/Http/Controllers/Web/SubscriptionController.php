<?php

namespace App\Http\Controllers\Web;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\PaymentMethod;
use App\Models\UserSubscription;
use App\Services\SubscriptionService;

class SubscriptionController extends Controller
{
    /**
     * Show the application subscriptions page.
     */
    public function subscriptions()
    {
        // Load subscription data
        $subscriptions = app(SubscriptionService::class)->loadMergedSubscriptions();
        return view('subscriptions', [
            'user' => null,
            'openApp' => null,
            'subscription' => null,
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * Show the application subscriptions page.
     */
    public function subscriptionsUser($token = null, Request $request)
    {
        $accessToken = PersonalAccessToken::findToken($token);
        $user = $accessToken?->tokenable;

        if (!$user) {
            return redirect('/subscriptions')
                ->with('error', 'Invalid or expired token. Please try again.');
        }

        $subscriptions = app(SubscriptionService::class)->loadMergedSubscriptions();
        $selectedPlan = null;
        $payload = $request->session()->get('payload');
        if ($user && is_array($payload)) {
            $planCode = $payload['code'] ?? null;

            if (!$planCode) {
                return Log::warning('Missing plan in payload', ['payload' => $payload]);
            }

            $selectedPlan = $subscriptions->firstWhere('code', $planCode);
            if (!$selectedPlan) {
                return Log::warning('Invalid plan code in payload', ['code' => $planCode]);
            }

            if (!$selectedPlan['subscription_id']) {
                return Log::warning('Missing subscription_id for plan', ['code' => $selectedPlan]);
            }

            $this->createSeededSubscription($user, $selectedPlan['subscription_id']);
        }

        return view('subscriptions', [
            'user' => $user,
            'openApp' => $accessToken ? 'classer://auth/login?token=' . $token : null,
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
        DB::transaction(function () use ($user, $subscriptionId) {
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
                'subscription_id' => $subscriptionId,
                'payment_method_id' => $paymentMethod->uid,
                'status' => 'active',
                'auto_renew' => true,
                'expiration_date' => now()->addMonths(6),
                'auto_renew_date' => now()->addMonths(6),
                'transaction_id' => 'pi_' . Str::random(16),
                'updated_by' => 'system',
                'notes' => 'Seeded subscription for testing',
            ]);
        });
    }
}
