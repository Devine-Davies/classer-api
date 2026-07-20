<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class CatalogItem extends Model
{
    protected $fillable = [
        'uid',
        'sellable_type',
        'sellable_id',
        'sku',
        'slug',
        'title',
        'short_description',
        'description',
        'price_amount',
        'promotion_percentage',
        'currency',
        'is_published',
        'image_url',
        'promotion_eligible',
        'discount_code_eligible',
        'shipping_required',
    ];

    protected $casts = [
        'price_amount' => 'integer',
        'promotion_percentage' => 'integer',
        'is_published' => 'boolean',
        'promotion_eligible' => 'boolean',
        'discount_code_eligible' => 'boolean',
        'shipping_required' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model and set up event listeners for creating, created, and updated events.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            $model->setAttributes([
                'uid' => $model->uid ?? (string) Str::uuid(),
                'slug' => $model->slug ?? Str::slug((string) $model->title).'-'.strtolower(substr((string) $model->uid, 0, 8)),
                'price_amount' => $model->price_amount ?? 0,
                'currency' => $model->currency ?? 'gbp',
                'is_published' => $model->is_published ?? false,
                'promotion_eligible' => $model->promotion_eligible ?? false,
                'discount_code_eligible' => $model->discount_code_eligible ?? false,
                'shipping_required' => $model->shipping_required ?? false,
                'short_description' => $model->short_description ?? '',
                'description' => $model->description ?? '',
            ]);
        });

        // static::updating(function (self $model) {
        //     if ($model->isDirty('currency')) {
        //         $model->currency = strtolower((string) ($model->currency ?: 'gbp'));
        //     }
        // });
    }

    /**
     * Get the sellable model (Plan, Product, etc.) associated with this catalog item.
     */
    public function sellable(): MorphTo
    {
        return $this->morphTo(name: 'sellable', type: 'sellable_type', id: 'sellable_id', ownerKey: 'uid');
    }

    /**
     * Calculate promotional pricing for this catalog item.
     *
     * Returns both unit and line amounts in minor currency units so callers
     * (checkout draft, order creation, order refresh, resources) share one
     * source of truth for promotion maths.
     *
     * @return array{original_unit_amount:int,unit_amount:int,promotion_percentage:int,original_line_amount:int,line_amount:int}
     */
    public function pricingBreakdown(int $quantity = 1): array
    {
        $quantity = max(1, $quantity);
        $originalUnitAmount = max(0, (int) $this->price_amount);
        $promotionPercentage = max(0, min(100, (int) $this->promotion_percentage));

        $unitAmount = $promotionPercentage > 0
            ? (int) floor($originalUnitAmount * ((100 - $promotionPercentage) / 100))
            : $originalUnitAmount;

        return [
            'original_unit_amount' => $originalUnitAmount,
            'unit_amount' => $unitAmount,
            'promotion_percentage' => $promotionPercentage,
            'original_line_amount' => $originalUnitAmount * $quantity,
            'line_amount' => $unitAmount * $quantity,
        ];
    }

    /**
     * Set default values and apply overrides for the catalog item.
     *
     * @param  array  $overrides  Key-value pairs to override default attributes.
     */
    protected function setAttributes(array $attributes): void
    {
        $normalizedAttributes = array_filter(
            $attributes,
            static fn ($value): bool => $value !== null
        );

        $this->fill($normalizedAttributes);
    }
}
