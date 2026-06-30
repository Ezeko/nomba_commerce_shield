<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class MoneyCast implements CastsAttributes
{
    /**
     * Cast the given value from Kobo (integer) to Naira (float).
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): float
    {
        return (float) ($value / 100);
    }

    /**
     * Prepare the given value for storage in Kobo (integer).
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): int
    {
        return (int) round((float) $value * 100);
    }
}
