<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'uid',
        'discount_code_id',
        'quantity',
        'amount',
        'subtotal_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'status',
        'customer_name',
        'customer_email',
        'shipping_line_1',
        'shipping_line_2',
        'shipping_city',
        'shipping_state',
        'shipping_postal_code',
        'shipping_country',
        'discount_snapshot',
        'paid_at',
    ];

    protected $casts = [
        'discount_snapshot' => 'array',
        'paid_at' => 'datetime',
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

    public function discountCode(): BelongsTo
    {
        return $this->belongsTo(DiscountCode::class, 'discount_code_id', 'uid');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class, 'order_id', 'uid');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'uid');
    }

    public function activePayment(): ?OrderPayment
    {
        return $this->payments()
            ->whereIn('status', ['pending', 'processing', 'requires_action'])
            ->latest('id')
            ->first();
    }
}
