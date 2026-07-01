<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EscrowEvent extends Model
{
    protected $fillable = [
        'escrow_id',
        'from_status',
        'to_status',
        'description',
        'created_by',
    ];

    /**
     * Get the escrow record associated with this event.
     */
    public function escrow(): BelongsTo
    {
        return $this->belongsTo(Escrow::class);
    }
}
