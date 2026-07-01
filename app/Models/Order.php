<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'store_id',
        'customer_id',
        'order_number',
        'total_amount',
        'payment_status',
        'payment_method',
        'shipping_status',
        'shipping_address',
        'customer_notes',
        'nomba_order_id',
        'nomba_payment_reference',
    ];

    protected $casts = [
        'total_amount' => \App\Casts\MoneyCast::class,
    ];

    /**
     * Get the store that received the order.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the customer who placed the order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the items in the order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the escrow record associated with the order (if payment_method is escrow).
     */
    public function escrow(): HasOne
    {
        return $this->hasOne(Escrow::class);
    }
}
