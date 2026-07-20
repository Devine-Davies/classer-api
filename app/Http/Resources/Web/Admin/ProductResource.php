<?php

namespace App\Http\Resources\Web\Admin;

use App\Http\Resources\CatalogItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $catalogItem = $this->catalogItem
            ? $this->buildCatalogItemData($request)
            : null;

        return [
            'uid' => $this->uid,
            'title' => $this->title,
            'code' => $this->code,
            'shortDescription' => $this->short_description,
            'catalogItem' => $catalogItem,
            'deletedAt' => $this->deleted_at,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }

    protected function buildCatalogItemData(Request $request): array
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

    protected function money(int $amount, string $currency): string
    {
        if ($currency === 'GBP') {
            return '£'.number_format($amount / 100, 2);
        }

        return $currency.' '.number_format($amount / 100, 2);
    }
}
