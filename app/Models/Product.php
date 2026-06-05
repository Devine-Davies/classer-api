<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'purchase_type',
        'price_amount',
        'promotion_percentage',
        'currency',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'promotion_percentage' => 'integer',
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->uid)) {
                $model->uid = (string) Str::uuid();
            }
        });
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'product_id', 'uid');
    }

    public function promotionDiscountAmount(): int
    {
        $percentage = max(0, min(100, (int) $this->promotion_percentage));

        return (int) floor(((int) $this->price_amount * $percentage) / 100);
    }

    public function discountedPriceAmount(): int
    {
        return max(0, (int) $this->price_amount - $this->promotionDiscountAmount());
    }
}
