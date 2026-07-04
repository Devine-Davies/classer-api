<?php

namespace App\Http\Resources\Web;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $hasPromotion = $this->promotion_percentage > 0;
        $currentPrice = $this->calculatePromotionPrice($this->price_amount, $this->promotion_percentage);

        return [
            'uid' => $this->uid,
            'sellableType' => $this->sellable_type,
            'sellableId' => $this->sellable_id,
            'sku' => $this->sku,
            'slug' => $this->slug,
            'title' => $this->title,
            'shortDescription' => $this->short_description,
            'description' => $this->description,
            'hasPromotion' => $hasPromotion,
            'originalPrice' => $this->price_amount,
            'currentPrice' => $currentPrice,
            'priceAmount' => $this->price_amount,
            'priceAmountFormatted' => number_format($currentPrice / 100, 2),
            'promotionPercentage' => $this->promotion_percentage,
            'currency' => $this->currency,
            'isPublished' => $this->is_published,
            'imageUrl' => $this->image_url,
            'promotionEligible' => $this->promotion_eligible,
            'discountCodeEligible' => $this->discount_code_eligible,
            'shippingRequired' => $this->shipping_required,
            'sellable' => $this->whenLoaded('sellable', function () {
                if (! $this->sellable) {
                    return null;
                }

                return [
                    'uid' => $this->sellable->uid,
                    'title' => $this->sellable->title ?? $this->sellable->name ?? null,
                    'code' => $this->sellable->code ?? null,
                    'slug' => $this->sellable->slug ?? null,
                ];
            }),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }

    function calculatePromotionPrice($priceAmount, $promotionPercentage)
    {
        if ($promotionPercentage > 0) {
            $discountAmount = ($priceAmount * $promotionPercentage) / 100;
            return $priceAmount - $discountAmount;
        }

        return $priceAmount;
    }
}