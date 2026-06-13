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
            'subtotal_amount' => $this->subtotal_amount,
            'discount_amount' => $this->discount_amount,
            'total_amount' => $this->total_amount,
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
                        'catalog_item_id' => $item->catalog_item_id,
                        'sku_snapshot' => $item->sku_snapshot,
                        'name_snapshot' => $item->name_snapshot,
                        'type' => $sellable ? class_basename($sellable) : null,
                        'unit_amount' => $item->unit_amount,
                        'quantity' => $item->quantity,
                        'line_amount' => $item->line_amount,
                        'currency' => $item->catalog_item?->currency ?? $this->currency,
                        'catalog_item' => $item->catalog_item ? [
                            'uid' => $item->catalog_item->uid,
                            'sku' => $item->catalog_item->sku,
                            'slug' => $item->catalog_item->slug,
                            'title' => $item->catalog_item->title,
                            'price_amount' => $item->catalog_item->price_amount,
                            'currency' => $item->catalog_item->currency,
                            'image_url' => $item->catalog_item->image_url,
                            'sellable_type' => $item->catalog_item->sellable_type,
                            'sellable_id' => $item->catalog_item->sellable_id,
                        ] : null,
                        'product' => $sellable instanceof Product ? [
                            'uid' => $sellable->uid,
                            'slug' => $sellable->slug,
                            'name' => $sellable->name,
                            'image_url' => $sellable->image_url,
                        ] : null,
                    ];
                })->values();
            }, []),
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'shipping' => [
                'line_1' => $this->shipping_line_1,
                'line_2' => $this->shipping_line_2,
                'city' => $this->shipping_city,
                'state' => $this->shipping_state,
                'postal_code' => $this->shipping_postal_code,
                'country' => $this->shipping_country,
            ],
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'catalog_item' => $this->catalog_item ? [
                'uid' => $this->catalog_item->uid,
                'sku' => $this->catalog_item->sku,
                'slug' => $this->catalog_item->slug,
                'title' => $this->catalog_item->title,
                'price_amount' => $this->catalog_item->price_amount,
                'currency' => $this->catalog_item->currency,
                'image_url' => $this->catalog_item->image_url,
                'sellable_type' => $this->catalog_item->sellable_type,
                'sellable_id' => $this->catalog_item->sellable_id,
            ] : null,
            'product' => $this->product ? [
                'uid' => $this->product->uid,
                'slug' => $this->product->slug,
                'name' => $this->product->name,
                'image_url' => $this->product->image_url,
            ] : null,
        ];
    }
}
