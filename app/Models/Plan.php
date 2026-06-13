<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class Plan extends Authenticatable
{
    protected $fillable = [
        'uid',
        'title',
        'code',
        'quota',
        'type',
        'duration',
    ];

    protected $table = 'plans';

    protected $hidden = [
        'id',
    ];

    protected $casts = [
        'quota' => 'integer',
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
        });

        static::saved(function (self $model) {
            $model->syncCatalogItem();
        });
    }

    /*
    * Ensure the code is always stored in uppercase.
    */
    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = strtoupper($value);
    }

    public function catalogItem(): MorphOne
    {
        return $this->morphOne(CatalogItem::class, 'sellable', 'sellable_type', 'sellable_id', 'uid');
    }

    public function syncCatalogItem(array $overrides = []): void
    {
        $defaultSku = 'PLAN-'.strtoupper((string) $this->code);
        $defaultSlug = Str::slug($this->title).'-'.strtolower((string) $this->code);

        $normalizedOverrides = array_filter(
            $overrides,
            static fn ($value) => $value !== null
        );

        $this->catalogItem()->updateOrCreate(
            [],
            array_merge([
                'sku' => $defaultSku,
                'slug' => $defaultSlug,
                'title' => (string) $this->title,
                'price_amount' => 0,
                'promotion_percentage' => 0,
                'currency' => 'gbp',
                'is_active' => true,
                'image_url' => null,
                'promotion_eligible' => true,
                'discount_code_eligible' => true,
                'shipping_required' => false,
            ], $normalizedOverrides)
        );
    }
}
