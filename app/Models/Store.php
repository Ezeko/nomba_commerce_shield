<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'logo_url',
        'virtual_account_number',
        'virtual_account_bank',
        'virtual_account_name',
        'balance',
        'trust_score',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'trust_score' => 'integer',
        'balance' => \App\Casts\MoneyCast::class,
    ];

    /**
     * Get the merchant user who owns the store.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the products in the store.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the customers of the store.
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Get the orders received by the store.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the withdrawals made by the store.
     */
    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }
}
