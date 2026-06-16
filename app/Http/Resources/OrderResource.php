<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

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
            'statusLabel' => $this->statusLabel($this->status),
            'statusClass' => $this->statusClass($this->status),

            'quantity' => $this->quantity,

            'amount' => $this->amount,
            'amountFormatted' => $this->money($this->amount, $this->currency),

            'subtotalAmount' => $this->subtotal_amount,
            'subtotalAmountFormatted' => $this->money($this->subtotal_amount, $this->currency),

            'discountAmount' => $this->discount_amount,
            'discountAmountFormatted' => $this->money($this->discount_amount, $this->currency),

            'totalAmount' => $this->total_amount,
            'totalAmountFormatted' => $this->money($this->total_amount, $this->currency),

            'currency' => $this->currency,

            'discount' => $this->discount_code_id ? [
                'uid' => $this->discount_code_id,
                'code' => $this->discount_snapshot['code'] ?? null,
                'percentage' => $this->discount_snapshot['percentage'] ?? null,
                'snapshot' => $this->discount_snapshot,
                'label' => trim(implode(' ', array_filter([
                    $this->discount_snapshot['code'] ?? null,
                    isset($this->discount_snapshot['percentage'])
                        ? '(' . $this->discount_snapshot['percentage'] . '%)'
                        : null,
                ]))),
            ] : null,

            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    $catalogItem = $item->catalog_item;
                    $sellable = $catalogItem?->sellable;
                    $currency = $catalogItem?->currency ?? $this->currency;

                    return [
                        'uid' => $item->uid,
                        'catalogItemId' => $item->catalog_item_id,

                        'skuSnapshot' => $item->sku_snapshot,
                        'nameSnapshot' => $item->name_snapshot,

                        'displayName' => $item->name_snapshot
                            ?: $catalogItem?->title
                            ?: 'Catalog item',

                        'displaySku' => $item->sku_snapshot
                            ?: $catalogItem?->sku
                            ?: $catalogItem?->uid
                            ?: '-',

                        'type' => $sellable ? class_basename($sellable) : null,

                        'unitAmount' => $item->unit_amount,
                        'unitAmountFormatted' => $this->money($item->unit_amount, $currency),

                        'quantity' => $item->quantity,

                        'lineAmount' => $item->line_amount,
                        'lineAmountFormatted' => $this->money($item->line_amount, $currency),

                        'currency' => $currency,

                        'catalogItem' => $catalogItem ? [
                            'uid' => $catalogItem->uid,
                            'sku' => $catalogItem->sku,
                            'slug' => $catalogItem->slug,
                            'title' => $catalogItem->title,
                            'priceAmount' => $catalogItem->price_amount,
                            'priceAmountFormatted' => $this->money($catalogItem->price_amount, $catalogItem->currency),
                            'currency' => $catalogItem->currency,
                            'imageUrl' => $catalogItem->image_url,
                            'sellableType' => $catalogItem->sellable_type,
                            'sellableId' => $catalogItem->sellable_id,
                        ] : null,

                        'product' => $sellable instanceof Product ? [
                            'uid' => $sellable->uid,
                            'slug' => $sellable->slug,
                            'name' => $sellable->name,
                            'imageUrl' => $sellable->image_url,
                            'displayName' => $sellable->name ?: $catalogItem?->title ?: 'Product',
                        ] : null,
                    ];
                })->values();
            }, []),

            'payments' => $this->whenLoaded('payments', function () {
                return $this->payments->map(function ($payment) {
                    $currency = $payment->currency ?? $this->currency;

                    return [
                        'uid' => $payment->uid,
                        'status' => $payment->status,
                        'statusLabel' => $this->statusLabel($payment->status),
                        'statusClass' => $this->statusClass($payment->status),

                        'amount' => $payment->amount,
                        'amountFormatted' => $this->money($payment->amount, $currency),
                        'currency' => $currency,

                        'stripePaymentIntentId' => $payment->stripe_payment_intent_id,
                        'stripePaymentMethodId' => $payment->stripe_payment_method_id,
                        'stripeCustomerId' => $payment->stripe_customer_id,

                        'failureCode' => $payment->failure_code,
                        'failureMessage' => $payment->failure_message,

                        'paidAt' => $payment->paid_at,
                        'paidAtFormatted' => $this->dateTime($payment->paid_at),

                        'refundedAt' => $payment->refunded_at,
                        'refundedAtFormatted' => $this->dateTime($payment->refunded_at),

                        'createdAt' => $payment->created_at,
                        'createdAtFormatted' => $this->dateTime($payment->created_at),
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
                'address' => $this->shippingAddress(),
            ],

            'paidAt' => $this->paid_at,
            'paidAtFormatted' => $this->dateTime($this->paid_at),

            'createdAt' => $this->created_at,
            'createdAtFormatted' => $this->dateTime($this->created_at),

            'updatedAt' => $this->updated_at,
            'updatedAtFormatted' => $this->dateTime($this->updated_at),

            'catalogItem' => $this->catalog_item ? [
                'uid' => $this->catalog_item->uid,
                'sku' => $this->catalog_item->sku,
                'slug' => $this->catalog_item->slug,
                'title' => $this->catalog_item->title,
                'priceAmount' => $this->catalog_item->price_amount,
                'priceAmountFormatted' => $this->money($this->catalog_item->price_amount, $this->catalog_item->currency),
                'currency' => $this->catalog_item->currency,
                'imageUrl' => $this->catalog_item->image_url,
                'sellableType' => $this->catalog_item->sellable_type,
                'sellableId' => $this->catalog_item->sellable_id,
                'displayName' => $this->catalog_item->title ?: $this->catalog_item->sku ?: $this->catalog_item->uid,
            ] : null,

            'product' => $this->product ? [
                'uid' => $this->product->uid,
                'slug' => $this->product->slug,
                'name' => $this->product->name,
                'imageUrl' => $this->product->image_url,
                'displayName' => $this->product->name ?: $this->product->uid,
            ] : null,
        ];
    }

    /**
     * Format amount in cents to a human-readable string with currency.
     */
    protected function money($amount, ?string $currency = 'GBP'): string
    {
        if ($amount === null || $amount === '') {
            return '-';
        }

        return strtoupper((string) ($currency ?: 'GBP')) . ' ' . number_format(((int) $amount) / 100, 2);
    }

    /**
     * Format a date/time value to a human-readable string.
     */
    protected function dateTime($value): string
    {
        if (! $value) {
            return '-';
        }

        try {
            return Carbon::parse($value)->format('d M Y, H:i');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    /**
     * Get a human-readable label for the order status.
     */
    protected function statusLabel(?string $status): string
    {
        return ucfirst(str_replace('_', ' ', (string) ($status ?: '-')));
    }

    /**
     * Get a CSS class for the order status.
     */
    protected function statusClass(?string $status): string
    {
        return match ((string) $status) {
            'paid', 'refunded' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'pending', 'processing', 'requires_action' => 'bg-amber-50 text-amber-700 border-amber-200',
            'failed', 'cancelled', 'canceled' => 'bg-rose-50 text-rose-700 border-rose-200',
            default => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }

    /**
     * Get the shipping address as a formatted string.
     */
    protected function shippingAddress(): string
    {
        $lines = array_filter([
            $this->shipping_line_1,
            $this->shipping_line_2,
            $this->shipping_city,
            $this->shipping_state,
            $this->shipping_postal_code,
            $this->shipping_country,
        ]);

        return $lines ? implode(', ', $lines) : '-';
    }
}