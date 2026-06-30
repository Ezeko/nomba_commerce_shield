<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdrawal extends Model
{
    protected $fillable = [
        'store_id',
        'amount',
        'bank_code',
        'bank_name',
        'account_number',
        'account_name',
        'status',
        'reference',
    ];

    protected $casts = [
        'amount' => \App\Casts\MoneyCast::class,
    ];

    /**
     * Get the store that made the withdrawal.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
