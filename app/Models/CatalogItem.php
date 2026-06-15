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
