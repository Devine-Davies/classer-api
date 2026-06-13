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
        'price_amount',
        'promotion_percentage',
        'currency',
        'is_active',
        'image_url',
        'promotion_eligible',
        'discount_code_eligible',
        'shipping_required',
    ];

    protected $casts = [
        'price_amount' => 'integer',
        'promotion_percentage' => 'integer',
        'is_active' => 'boolean',
        'promotion_eligible' => 'boolean',
        'discount_code_eligible' => 'boolean',
        'shipping_required' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->uid)) {
                $model->uid = (string) Str::uuid();
            }

            $model->currency = strtolower((string) ($model->currency ?: 'gbp'));
        });

        static::updating(function (self $model) {
            if ($model->isDirty('currency')) {
                $model->currency = strtolower((string) ($model->currency ?: 'gbp'));
            }
        });
    }

    public function sellable(): MorphTo
    {
        return $this->morphTo(name: 'sellable', type: 'sellable_type', id: 'sellable_id', ownerKey: 'uid');
    }
}
