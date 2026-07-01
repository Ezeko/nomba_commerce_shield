<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'store_id',
        'name',
        'email',
        'phone',
    ];

    /**
     * Get the store associated with the customer.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the orders placed by the customer.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
