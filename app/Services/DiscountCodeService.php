<?php

namespace App\Services;

use App\Logging\AppLogger;
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

    /**
     * Create discount code service with logger context.
     *
     * @param  AppLogger  $logger  Application logger wrapper.
     */
    public function __construct(protected AppLogger $logger)
    {
        $this->logger->setContext('DiscountCodeService');
    }

    /**
     * Apply or remove a discount code in preview mode.
     *
     * @param  Order  $order  Order being updated.
     * @param  string|null  $discountCodeInput  Raw discount code input.
     * @param  string|null  $customerEmail  Customer email for eligibility checks.
     * @param  string|null  $userUid  Authenticated user UID when available.
     * @param  bool  $strictCustomerChecks  Whether to enforce user/email ownership checks.
     * @return Order Updated order.
     *
     * @throws ValidationException
     */
    public function applyPreview(
        Order $order,
        ?string $discountCodeInput,
        ?string $customerEmail = null,
        ?string $userUid = null,
        bool $strictCustomerChecks = false
    ): Order {
        if (! $discountCodeInput) {
            $this->logger->info('Clearing discount preview due to empty input', [
                'order_uid' => $order->uid,
            ]);

            return $this->clearDiscount($order);
        }

        return $this->applyAndPersist($order, $discountCodeInput, $customerEmail, $userUid, $strictCustomerChecks);
    }

    /**
     * Finalize discount validation before payment intent creation.
     *
     * @param  Order  $order  Order being finalized.
     * @param  string|null  $discountCodeInput  Requested discount code.
     * @param  string  $customerEmail  Customer email used for strict checks.
     * @param  string|null  $userUid  Authenticated user UID when available.
     * @return Order Updated order.
     *
     * @throws ValidationException
     */
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

        if (! $requestedCode) {
            return $this->clearDiscount($order);
        }

        return $this->applyAndPersist($order, $requestedCode, $customerEmail, $userUid, true);
    }

    /**
     * Validate and persist discount effects on an order.
     *
     * @param  Order  $order  Order being modified.
     * @param  string  $discountCodeInput  Discount code text.
     * @param  string|null  $customerEmail  Customer email.
     * @param  string|null  $userUid  Authenticated user UID.
     * @param  bool  $strictCustomerChecks  Whether strict customer ownership checks apply.
     * @return Order Updated order with discount snapshot.
     *
     * @throws ValidationException
     */
    protected function applyAndPersist(
        Order $order,
        string $discountCodeInput,
        ?string $customerEmail,
        ?string $userUid,
        bool $strictCustomerChecks
    ): Order {
        $order->loadMissing(['items.catalogItem', 'discountCode']);

        $logContext = [
            'order_uid' => $order->uid,
            'discount_code_input' => strtoupper(trim($discountCodeInput)),
            'customer_email' => $customerEmail ? strtolower($customerEmail) : null,
            'user_uid' => $userUid,
            'strict_customer_checks' => $strictCustomerChecks,
        ];

        $discountCode = DiscountCode::whereRaw('UPPER(code) = ?', [strtoupper(trim($discountCodeInput))])->first();
        if (! $discountCode) {
            $this->logger->warning('Discount validation failed: code not found', $logContext);
            $this->throwDiscountValidation(self::REASON_INVALID);
        }

        $now = now();

        if (! $discountCode->is_active || $discountCode->disabled_at !== null) {
            $this->logger->warning('Discount validation failed: code inactive or disabled', array_merge($logContext, [
                'discount_code_uid' => $discountCode->uid,
            ]));
            $this->throwDiscountValidation(self::REASON_NOT_ELIGIBLE);
        }

        if ($discountCode->starts_at && $discountCode->starts_at->gt($now)) {
            $this->logger->warning('Discount validation failed: code not started yet', array_merge($logContext, [
                'discount_code_uid' => $discountCode->uid,
                'starts_at' => $discountCode->starts_at?->toIso8601String(),
            ]));
            $this->throwDiscountValidation(self::REASON_EXPIRED);
        }

        if ($discountCode->expires_at && $discountCode->expires_at->lt($now)) {
            $this->logger->warning('Discount validation failed: code expired', array_merge($logContext, [
                'discount_code_uid' => $discountCode->uid,
                'expires_at' => $discountCode->expires_at?->toIso8601String(),
            ]));
            $this->throwDiscountValidation(self::REASON_EXPIRED);
        }

        if ($discountCode->usage_limit !== null && $discountCode->usage_count >= $discountCode->usage_limit) {
            $this->logger->warning('Discount validation failed: usage limit reached', array_merge($logContext, [
                'discount_code_uid' => $discountCode->uid,
                'usage_count' => $discountCode->usage_count,
                'usage_limit' => $discountCode->usage_limit,
            ]));
            $this->throwDiscountValidation(self::REASON_LIMIT_REACHED);
        }

        if ($discountCode->catalog_item_id && ! $order->items->contains(fn ($item) => $item->catalog_item_id === $discountCode->catalog_item_id)) {
            $this->logger->warning('Discount validation failed: catalog item mismatch', array_merge($logContext, [
                'discount_code_uid' => $discountCode->uid,
                'required_catalog_item_uid' => $discountCode->catalog_item_id,
            ]));
            $this->throwDiscountValidation(self::REASON_NOT_ELIGIBLE);
        }

        if ($strictCustomerChecks) {
            if ($discountCode->assigned_user_id && $discountCode->assigned_user_id !== $userUid) {
                $this->logger->warning('Discount validation failed: assigned user mismatch', array_merge($logContext, [
                    'discount_code_uid' => $discountCode->uid,
                    'assigned_user_uid' => $discountCode->assigned_user_id,
                ]));
                $this->throwDiscountValidation(self::REASON_NOT_ELIGIBLE);
            }

            if ($discountCode->assigned_email && strcasecmp((string) $discountCode->assigned_email, (string) $customerEmail) !== 0) {
                $this->logger->warning('Discount validation failed: assigned email mismatch', array_merge($logContext, [
                    'discount_code_uid' => $discountCode->uid,
                    'assigned_email' => strtolower((string) $discountCode->assigned_email),
                ]));
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
                    $this->logger->warning('Discount validation failed: one-use-per-customer already redeemed', array_merge($logContext, [
                        'discount_code_uid' => $discountCode->uid,
                    ]));
                    $this->throwDiscountValidation(self::REASON_NOT_ELIGIBLE);
                }
            }
        }

        $subtotalAmount = $this->resolveSubtotal($order);

        if ($discountCode->min_order_amount !== null && $subtotalAmount < $discountCode->min_order_amount) {
            $this->logger->warning('Discount validation failed: minimum order not met', array_merge($logContext, [
                'discount_code_uid' => $discountCode->uid,
                'subtotal_amount' => $subtotalAmount,
                'minimum_order_amount' => $discountCode->min_order_amount,
            ]));
            $this->throwDiscountValidation(self::REASON_MINIMUM_NOT_MET);
        }

        $effectivePercentage = (int) $discountCode->discount_percentage;
        if ($discountCode->max_discount_percentage !== null) {
            $effectivePercentage = min($effectivePercentage, (int) $discountCode->max_discount_percentage);
        }

        $discountAmount = (int) floor(($subtotalAmount * $effectivePercentage) / 100);

        if ($discountAmount >= $subtotalAmount) {
            $this->logger->warning('Discount validation failed: discount would zero total', array_merge($logContext, [
                'discount_code_uid' => $discountCode->uid,
                'subtotal_amount' => $subtotalAmount,
                'discount_amount' => $discountAmount,
            ]));
            $this->throwDiscountValidation(self::REASON_TOTAL_ZERO);
        }

        $totalAmount = max(0, $subtotalAmount - $discountAmount);

        if ($totalAmount <= 0) {
            $this->logger->warning('Discount validation failed: computed total non-positive', array_merge($logContext, [
                'discount_code_uid' => $discountCode->uid,
                'subtotal_amount' => $subtotalAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
            ]));
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

        $this->logger->info('Discount applied to order', [
            'order_uid' => $order->uid,
            'discount_code_uid' => $discountCode->uid,
            'discount_code' => $discountCode->code,
            'discount_percentage' => $effectivePercentage,
            'subtotal_amount' => $subtotalAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
        ]);

        return $order->fresh(['items.catalogItem', 'discountCode']);
    }

    /**
     * Clear discount state and reset order totals to subtotal.
     *
     * @param  Order  $order  Order to reset.
     * @return Order Updated order.
     */
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

        $this->logger->info('Discount cleared from order', [
            'order_uid' => $order->uid,
            'subtotal_amount' => $subtotalAmount,
        ]);

        return $order->fresh(['items.catalogItem', 'discountCode']);
    }

    /**
     * Throw a standardized discount validation exception.
     *
     * @param  string  $reasonCode  Internal machine-readable reason code.
     *
     * @throws ValidationException
     */
    public function throwDiscountValidation(string $reasonCode): void
    {
        $this->logger->warning('Throwing discount validation exception', [
            'reason_code' => $reasonCode,
        ]);

        throw ValidationException::withMessages([
            'discount_code' => ['Discount code is not eligible.'],
            'reason_code' => [$reasonCode],
        ]);
    }

    /**
     * Resolve the subtotal amount from order line items.
     *
     * @param  Order  $order  Order containing line items.
     * @return int Subtotal in minor currency units.
     */
    protected function resolveSubtotal(Order $order): int
    {
        return (int) $order->items->sum('line_amount');
    }
}
