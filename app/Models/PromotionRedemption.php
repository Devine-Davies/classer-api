<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PromotionRedemption extends Model
{
    protected $fillable = [
        'uid',
        'promotion_code',
        'source_type',
        'source_uid',
        'order_id',
        'order_item_id',
        'user_id',
        'customer_email',
        'status',
        'redeem_token_hash',
        'sent_at',
        'redeemed_at',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'redeemed_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'uid');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id', 'uid');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uid');
    }
}
