<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OrderPayment extends Model
{
    protected $fillable = [
        'uid',
        'order_id',
        'stripe_payment_intent_id',
        'stripe_payment_method_id',
        'stripe_customer_id',
        'status',
        'amount',
        'currency',
        'failure_code',
        'failure_message',
        'paid_at',
        'refunded_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
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
}
