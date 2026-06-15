<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'uid' => $this->uid,
            'status' => $this->status,
            'quantity' => $this->quantity,
            'amount' => $this->amount,
            'subtotalAmount' => $this->subtotal_amount,
            'discountAmount' => $this->discount_amount,
            'totalAmount' => $this->total_amount,
            'currency' => $this->currency,
            'discount' => $this->discount_code_id ? [
                'uid' => $this->discount_code_id,
                'code' => $this->discount_snapshot['code'] ?? null,
                'percentage' => $this->discount_snapshot['percentage'] ?? null,
                'snapshot' => $this->discount_snapshot,
            ] : null,
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    $sellable = $item->catalog_item?->sellable;

                    return [
                        'uid' => $item->uid,
                        'catalogItemId' => $item->catalog_item_id,
                        'skuSnapshot' => $item->sku_snapshot,
                        'nameSnapshot' => $item->name_snapshot,
                        'type' => $sellable ? class_basename($sellable) : null,
                        'unitAmount' => $item->unit_amount,
                        'quantity' => $item->quantity,
                        'lineAmount' => $item->line_amount,
                        'currency' => $item->catalog_item?->currency ?? $this->currency,
                        'catalogItem' => $item->catalog_item ? [
                            'uid' => $item->catalog_item->uid,
                            'sku' => $item->catalog_item->sku,
                            'slug' => $item->catalog_item->slug,
                            'title' => $item->catalog_item->title,
                            'priceAmount' => $item->catalog_item->price_amount,
                            'currency' => $item->catalog_item->currency,
                            'imageUrl' => $item->catalog_item->image_url,
                            'sellableType' => $item->catalog_item->sellable_type,
                            'sellableId' => $item->catalog_item->sellable_id,
                        ] : null,
                        'product' => $sellable instanceof Product ? [
                            'uid' => $sellable->uid,
                            'slug' => $sellable->slug,
                            'name' => $sellable->name,
                            'imageUrl' => $sellable->image_url,
                        ] : null,
                    ];
                })->values();
            }, []),
            'customerName' => $this->customer_name,
            'customerEmail' => $this->customer_email,
            'shipping' => [
                'line1' => $this->shipping_line_1,
                'line2' => $this->shipping_line_2,
                'city' => $this->shipping_city,
                'state' => $this->shipping_state,
                'postalCode' => $this->shipping_postal_code,
                'country' => $this->shipping_country,
            ],
            'paidAt' => $this->paid_at,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'catalogItem' => $this->catalog_item ? [
                'uid' => $this->catalog_item->uid,
                'sku' => $this->catalog_item->sku,
                'slug' => $this->catalog_item->slug,
                'title' => $this->catalog_item->title,
                'priceAmount' => $this->catalog_item->price_amount,
                'currency' => $this->catalog_item->currency,
                'imageUrl' => $this->catalog_item->image_url,
                'sellableType' => $this->catalog_item->sellable_type,
                'sellableId' => $this->catalog_item->sellable_id,
            ] : null,
            'product' => $this->product ? [
                'uid' => $this->product->uid,
                'slug' => $this->product->slug,
                'name' => $this->product->name,
                'imageUrl' => $this->product->image_url,
            ] : null,
        ];
    }
}
