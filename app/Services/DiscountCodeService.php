<?php

namespace App\Services;

use App\Models\DiscountCode;
use App\Models\DiscountCodeRedemption;
use App\Models\Order;
use Illuminate\Validation\ValidationException;

class DiscountCodeService
{
    public const REASON_INVALID = 'INVALID_CODE';
    public const REASON_EXPIRED = 'EXPIRED_CODE';
    public const REASON_NOT_ELIGIBLE = 'CODE_NOT_ELIGIBLE';
    public const REASON_LIMIT_REACHED = 'USAGE_LIMIT_REACHED';
    public const REASON_MINIMUM_NOT_MET = 'MINIMUM_ORDER_NOT_MET';
    public const REASON_TOTAL_ZERO = 'CANNOT_REDUCE_TOTAL_TO_ZERO';

    public function applyPreview(
        Order $order,
        ?string $discountCodeInput,
        ?string $customerEmail = null,
        ?string $userUid = null,
        bool $strictCustomerChecks = false
    ): Order
    {
        if (!$discountCodeInput) {
            return $this->clearDiscount($order);
        }

        return $this->applyAndPersist($order, $discountCodeInput, $customerEmail, $userUid, $strictCustomerChecks);
    }

    public function finalizeForPaymentIntent(
        Order $order,
        ?string $discountCodeInput,
        string $customerEmail,
        ?string $userUid = null
    ): Order {
        $requestedCode = $discountCodeInput;

        if ($requestedCode === null && $order->discountCode) {
            $requestedCode = (string) $order->discountCode->code;
        }

        if (!$requestedCode) {
            return $this->clearDiscount($order);
        }

        return $this->applyAndPersist($order, $requestedCode, $customerEmail, $userUid, true);
    }

    protected function applyAndPersist(
        Order $order,
        string $discountCodeInput,
        ?string $customerEmail,
        ?string $userUid,
        bool $strictCustomerChecks
    ): Order {
        $order->loadMissing(['items', 'discountCode']);

        $discountCode = DiscountCode::whereRaw('UPPER(code) = ?', [strtoupper(trim($discountCodeInput))])->first();
        if (!$discountCode) {
            $this->throwDiscountValidation(self::REASON_INVALID);
        }

        $now = now();

        if (!$discountCode->is_active || $discountCode->disabled_at !== null) {
            $this->throwDiscountValidation(self::REASON_NOT_ELIGIBLE);
        }

        if ($discountCode->starts_at && $discountCode->starts_at->gt($now)) {
            $this->throwDiscountValidation(self::REASON_EXPIRED);
        }

        if ($discountCode->expires_at && $discountCode->expires_at->lt($now)) {
            $this->throwDiscountValidation(self::REASON_EXPIRED);
        }

        if ($discountCode->usage_limit !== null && $discountCode->usage_count >= $discountCode->usage_limit) {
            $this->throwDiscountValidation(self::REASON_LIMIT_REACHED);
        }

        if ($discountCode->product_id && !$order->items->contains(fn ($item) => $item->product_id === $discountCode->product_id)) {
            $this->throwDiscountValidation(self::REASON_NOT_ELIGIBLE);
        }

        if ($strictCustomerChecks) {
            if ($discountCode->assigned_user_id && $discountCode->assigned_user_id !== $userUid) {
                $this->throwDiscountValidation(self::REASON_NOT_ELIGIBLE);
            }

            if ($discountCode->assigned_email && strcasecmp((string) $discountCode->assigned_email, (string) $customerEmail) !== 0) {
                $this->throwDiscountValidation(self::REASON_NOT_ELIGIBLE);
            }

            if ($discountCode->one_use_per_customer) {
                $usedByCustomer = DiscountCodeRedemption::where('discount_code_id', $discountCode->uid)
                    ->where(function ($query) use ($customerEmail, $userUid) {
                        if ($userUid) {
                            $query->orWhere('user_id', $userUid);
                        }

                        if ($customerEmail) {
                            $query->orWhereRaw('LOWER(customer_email) = ?', [strtolower($customerEmail)]);
                        }
                    })
                    ->exists();

                if ($usedByCustomer) {
                    $this->throwDiscountValidation(self::REASON_NOT_ELIGIBLE);
                }
            }
        }

        $subtotalAmount = $this->resolveSubtotal($order);

        if ($discountCode->min_order_amount !== null && $subtotalAmount < $discountCode->min_order_amount) {
            $this->throwDiscountValidation(self::REASON_MINIMUM_NOT_MET);
        }

        $effectivePercentage = (int) $discountCode->discount_percentage;
        if ($discountCode->max_discount_percentage !== null) {
            $effectivePercentage = min($effectivePercentage, (int) $discountCode->max_discount_percentage);
        }

        $discountAmount = (int) floor(($subtotalAmount * $effectivePercentage) / 100);

        if ($discountAmount >= $subtotalAmount) {
            $this->throwDiscountValidation(self::REASON_TOTAL_ZERO);
        }

        $totalAmount = max(0, $subtotalAmount - $discountAmount);

        if ($totalAmount <= 0) {
            $this->throwDiscountValidation(self::REASON_TOTAL_ZERO);
        }

        $order->fill([
            'discount_code_id' => $discountCode->uid,
            'subtotal_amount' => $subtotalAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'amount' => $totalAmount,
            'discount_snapshot' => [
                'uid' => $discountCode->uid,
                'code' => $discountCode->code,
                'percentage' => $effectivePercentage,
                'subtotal_amount' => $subtotalAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'currency' => $order->currency,
                'validated_at' => $now->toIso8601String(),
            ],
        ]);

        $order->save();

        return $order->fresh(['product', 'items.product', 'discountCode']);
    }

    public function clearDiscount(Order $order): Order
    {
        $order->loadMissing('items');

        $subtotalAmount = $this->resolveSubtotal($order);

        $order->fill([
            'discount_code_id' => null,
            'subtotal_amount' => $subtotalAmount,
            'discount_amount' => 0,
            'total_amount' => $subtotalAmount,
            'amount' => $subtotalAmount,
            'discount_snapshot' => null,
        ]);

        $order->save();

        return $order->fresh(['product', 'items.product', 'discountCode']);
    }

    public function throwDiscountValidation(string $reasonCode): void
    {
        throw ValidationException::withMessages([
            'discount_code' => ['Discount code is not eligible.'],
            'reason_code' => [$reasonCode],
        ]);
    }

    protected function resolveSubtotal(Order $order): int
    {
        return (int) $order->items->sum('line_amount');
    }
}
