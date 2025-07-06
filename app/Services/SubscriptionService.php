<?php

namespace App\Services;

use App\Http\Controllers\SystemController;
use App\Models\Subscription;
use Illuminate\Support\Collection;

class SubscriptionService
{
    /**
     * Load subscription dataset and merge with DB records.
     *
     * @return Collection
     */
    public function loadMergedSubscriptions(): Collection
    {
        $systemController = new SystemController();

        $resourceSubscriptions = collect(
            $systemController->loadFromResource('subscriptions.dataset.json')
        );

        $dbSubscriptions = Subscription::all()->keyBy('code');

        return $resourceSubscriptions->map(function ($item) use ($dbSubscriptions) {
            $match = $dbSubscriptions->get($item['code']);
            $item['subscription_id'] = $match?->uid;
            return $item;
        });
    }
}
