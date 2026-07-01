<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Escrow extends Model
{
    protected $fillable = [
        'order_id',
        'amount',
        'status',
        'escrow_wallet_reference',
        'release_code',
        'dispute_reason',
        'disputed_at',
        'released_at',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => \App\Casts\MoneyCast::class,
        'disputed_at' => 'datetime',
        'released_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    /**
     * Get the order associated with the escrow.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the history of events for this escrow.
     */
    public function events(): HasMany
    {
        return $this->hasMany(EscrowEvent::class);
    }
}
