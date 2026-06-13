<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DiscountCode extends Model
{
    protected $fillable = [
        'uid',
        'code',
        'discount_percentage',
        'max_discount_percentage',
        'min_order_amount',
        'catalog_item_id',
        'assigned_user_id',
        'assigned_email',
        'is_active',
        'usage_limit',
        'usage_count',
        'one_use_per_customer',
        'starts_at',
        'expires_at',
        'disabled_at',
        'internal_note',
        'created_by_user_id',
        'updated_by_user_id',
        'disabled_by_user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'one_use_per_customer' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'disabled_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->uid)) {
                $model->uid = (string) Str::uuid();
            }

            $model->code = strtoupper(trim((string) $model->code));
        });

        static::updating(function (self $model) {
            if ($model->isDirty('code')) {
                $model->code = strtoupper(trim((string) $model->code));
            }
        });
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(DiscountCodeRedemption::class, 'discount_code_id', 'uid');
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(CatalogItem::class, 'catalog_item_id', 'uid');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id', 'uid');
    }
}
