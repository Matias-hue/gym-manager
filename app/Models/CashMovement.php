<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToGym;

class CashMovement extends Model
{
    use BelongsToGym;
    
    protected $guarded = [];

    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    // Un movimiento de un pago anulado se muestra tachado y no suma
    public function isVoided(): bool
    {
        return $this->payment?->voided_at !== null;
    }
}
