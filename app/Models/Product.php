<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uid',
        'sku',
        'slug',
        'name',
        'short_description',
        'long_description',
        'description',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->uid)) {
                $model->uid = (string) Str::uuid();
            }

            if (empty($model->sku)) {
                $model->sku = 'SKU-'.strtoupper(Str::random(10));
            }

            if (empty($model->slug)) {
                $model->slug = Str::slug((string) $model->name).'-'.strtolower(substr((string) $model->uid, 0, 8));
            }
        });

        // static::saved(function (self $model) {
        //     $model->syncCatalogItem();
        // });
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'product_id', 'uid');
    }

    public function catalogItem(): MorphOne
    {
        return $this->morphOne(CatalogItem::class, 'sellable', 'sellable_type', 'sellable_id', 'uid');
    }

    public function syncCatalogItem(): void
    {
        $existingCatalogItem = $this->catalogItem()->first();

        $this->catalogItem()->updateOrCreate(
            [],
            [
                'sku' => (string) $this->sku,
                'slug' => (string) $this->slug,
                'title' => (string) $this->name,
                'price_amount' => (int) ($existingCatalogItem?->price_amount ?? 0),
                'promotion_percentage' => max(0, min(100, (int) ($existingCatalogItem?->promotion_percentage ?? 0))),
                'currency' => strtolower((string) ($existingCatalogItem?->currency ?? 'gbp')),
                'is_active' => (bool) $this->is_active,
                'image_url' => $this->image_url,
                'promotion_eligible' => true,
                'discount_code_eligible' => true,
                'shipping_required' => true,
            ]
        );
    }
}
