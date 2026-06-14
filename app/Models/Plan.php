<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;

class Plan extends Model
{
    protected $table = 'plans';

    protected $fillable = [
        'title',
        'code',
        'quota',
        'type',
        'duration',
        'short_description',
        'description',
    ];

    protected $hidden = [
        'id',
    ];

    protected $casts = [
        'quota' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Temporary catalog item payload used during create/update flows.
     */
    public array $catalogItemData = [];

    /**
     * Boot the model and set up event listeners for creating, created, and updated events.
     */
    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->uid)) {
                $model->uid = (string) Str::uuid();
            }

            if (empty($model->code)) {
                $model->code = strtoupper(Str::random(8));
            }
        });

        static::created(function (self $model): void {
            $model->syncCatalogItem([
                'title' => (string) $model->title,
                'sku' => 'PLAN-'.strtoupper((string) $model->code),
                'slug' => Str::slug((string) $model->title).'-'.strtolower((string) $model->code),
            ]);
        });

        // static::updated(function (self $model): void {
        //     if ($model->catalogItemData !== []) {
        //         $model->syncCatalogItem($model->catalogItemData);
        //     }
        // });
    }

    /**
     * Get the catalog item associated with the plan.
     */
    public function catalogItem(): MorphOne
    {
        return $this->morphOne(
            CatalogItem::class,
            'sellable',
            'sellable_type',
            'sellable_id',
            'uid'
        );
    }

    /**
     * Sync the plan with a catalog item.
     */
    public function syncCatalogItem(array $overrides = []): void
    {
        $defaults = [
            'price_amount' => 0,
            'currency' => 'gbp',
            'is_published' => false,
            'promotion_eligible' => false,
            'discount_code_eligible' => false,
            'shipping_required' => false,
            'short_description' => '',
            'description' => '',
        ];

        $normalizedOverrides = array_filter(
            $overrides,
            static fn ($value): bool => $value !== null
        );

        $attributes = array_merge($defaults, $normalizedOverrides);

        $this->catalogItem()->updateOrCreate(
            [],
            $attributes
        );
    }
}
