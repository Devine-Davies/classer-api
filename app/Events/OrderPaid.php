<?php

namespace App\Events;

use App\Models\Order;
use App\Models\OrderPayment;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPaid implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new order paid event.
     *
     * @param  Order  $order  Paid order.
     * @param  OrderPayment  $payment  Payment that completed the order.
     */
    public function __construct(
        public Order $order,
        public OrderPayment $payment
    ) {}
}
