<?php

namespace App\Services;

use App\Http\Controllers\SystemController;
use App\Logging\AppLogger;
use App\Models\Subscription;
use Illuminate\Support\Collection;

class SubscriptionService
{
    /**
     * Create subscription service with logger context.
     *
     * @param  AppLogger  $logger  Application logger wrapper.
     */
    public function __construct(protected AppLogger $logger)
    {
        $this->logger->setContext('SubscriptionService');
    }

    /**
     * Load subscription dataset and merge with DB records.
     */
    public function loadMergedSubscriptions(): Collection
    {
        $systemController = new SystemController;

        $resourceSubscriptions = collect(
            $systemController->loadFromResource('subscriptions.dataset.json')
        );

        $dbSubscriptions = Subscription::all()->keyBy('code');

        $this->logger->info('Merging subscription datasets', [
            'resource_count' => $resourceSubscriptions->count(),
            'db_count' => $dbSubscriptions->count(),
        ]);

        $merged = $resourceSubscriptions->map(function ($item) use ($dbSubscriptions) {
            $match = $dbSubscriptions->get($item['code']);
            $item['subscription_id'] = $match?->uid;

            return $item;
        });

        $this->logger->info('Merged subscription dataset complete', [
            'merged_count' => $merged->count(),
            'matched_count' => $merged->filter(fn ($item) => ! empty($item['subscription_id']))->count(),
        ]);

        return $merged;
    }
}
