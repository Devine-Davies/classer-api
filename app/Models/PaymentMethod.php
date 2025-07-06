<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $hidden = [
        'id',
    ];

    protected $fillable = [
        'uid',
        'user_id',
        'type',
        'brand',
        'last4',
        'exp_month',
        'exp_year',
        'stripe_customer_id',
        'stripe_payment_method_id',
        'stripe_transaction_id',
        'is_default',
        'revoked_at',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'revoked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get only active payment methods.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('revoked_at');
    }
}