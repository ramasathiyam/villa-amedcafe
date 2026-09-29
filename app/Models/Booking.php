<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Stage 2: availability only — no guest, price, rate-plan or payment columns yet.
 * Later stages extend this table rather than replacing it.
 */
#[Fillable(['code', 'status', 'expires_at'])]
class Booking extends Model
{
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }
}
