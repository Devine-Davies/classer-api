<?php

namespace App\Http\Resources\Web\Admin;

use App\Http\Resources\CatalogItemResource;
use App\Services\CurrencyPricingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $this->loadMissing(['catalogItem', 'entitlements']);
        $catalogItem = $this->catalogItem;

        return [
            'uid' => $this->uid,
            'code' => $this->code,
            'title' => $this->title,
            'duration' => $this->duration,
            'niceDuration' => $this->formatDuration($this->duration),
            'entitlements' => $this->entitlements->map(fn ($entitlement): array => [
                'capability' => $entitlement->capability,
                'quota' => $entitlement->quota,
                'niceQuota' => $this->formatQuota($entitlement->quota),
            ])->values(),
            'shortDescription' => $this->short_description,
            'description' => $this->description,
            'catalogItem' => $catalogItem
                ? $this->buildCatalogItemData($request)
                : null,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }

    private function buildCatalogItemData(Request $request): array
    {
        $catalogItemData = (new CatalogItemResource($this->catalogItem))->toArray($request);

        $baseAmount = max(0, (int) ($this->catalogItem->price_amount ?? 0));
        $promotionPercentage = max(0, min(100, (int) ($this->catalogItem->promotion_percentage ?? 0)));

        $discountAmount = (int) floor($baseAmount * ($promotionPercentage / 100));
        $discountedAmount = max(0, $baseAmount - $discountAmount);
        $hasDiscount = $promotionPercentage > 0 && $discountAmount > 0;

        $currency = strtoupper((string) ($this->catalogItem->currency ?: 'GBP'));
        $displayAmount = $hasDiscount ? $discountedAmount : $baseAmount;

        $catalogItemData['pricing'] = [
            'currency' => $currency,
            'hasDiscount' => $hasDiscount,
            'baseAmount' => $baseAmount,
            'discountedAmount' => $discountedAmount,
            'discountAmount' => $discountAmount,
            'promotionPercentage' => $promotionPercentage,
            'basePriceFormatted' => $this->money($baseAmount, $currency),
            'discountedPriceFormatted' => $this->money($discountedAmount, $currency),
            'discountAmountFormatted' => $this->money($discountAmount, $currency),
            'displayPriceFormatted' => $displayAmount === 0
                ? 'FREE'
                : $this->money($displayAmount, $currency),
        ];

        return $catalogItemData;
    }

    /*
    * Format the duration in a human-readable format.
    *
    * @param int|null $duration
    * @return string
    */
    private function formatDuration(?int $duration): string
    {
        if ($duration === null) {
            return 'Unlimited';
        }

        if ($duration < 60) {
            return $duration.' seconds';
        }

        if ($duration < 3600) {
            return number_format($duration / 60, 2).' minutes';
        }

        if ($duration < 86400) {
            return number_format($duration / 3600, 2).' hours';
        }

        return number_format($duration / 86400, 2).' days';
    }

    /*
    * Format the quota in a human-readable format.
    * @param int|null $quota
    * @return string
    */
    private function formatQuota(?int $quota): string
    {
        if ($quota === null) {
            return 'Unlimited';
        }

        if ($quota < 1024) {
            return $quota.' bytes';
        }

        if ($quota < 1048576) {
            return number_format($quota / 1024, 2).' KB';
        }

        if ($quota < 1073741824) {
            return number_format($quota / 1048576, 2).' MB';
        }

        return number_format($quota / 1073741824, 2).' GB';
    }

    private function money(int $amount, string $currency): string
    {
        return app(CurrencyPricingService::class)->format($amount, $currency);
    }
}
