<?php

namespace App\Http\Resources;

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
                    return [
                        'uid' => $item->uid,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name,
                        'purchase_type' => $item->purchase_type,
                        'unit_amount' => $item->unit_amount,
                        'quantity' => $item->quantity,
                        'line_amount' => $item->line_amount,
                        'currency' => $item->currency,
                        'product' => $item->product ? [
                            'uid' => $item->product->uid,
                            'slug' => $item->product->slug,
                            'name' => $item->product->name,
                            'purchase_type' => $item->product->purchase_type,
                            'price_amount' => $item->product->price_amount,
                            'currency' => $item->product->currency,
                            'image_url' => $item->product->image_url,
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
            'product' => $this->product ? [
                'uid' => $this->product->uid,
                'slug' => $this->product->slug,
                'name' => $this->product->name,
                'purchase_type' => $this->product->purchase_type,
                'price_amount' => $this->product->price_amount,
                'currency' => $this->product->currency,
                'image_url' => $this->product->image_url,
            ] : null,
        ];
    }
}
