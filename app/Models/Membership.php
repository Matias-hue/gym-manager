<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Membership extends Model
{
    protected $guarded = [];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
        'grace_until' => 'date',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    // current | grace | expired | cancelled
    public function state(int $graceDays = 14): string
    {
        if ($this->status === 'cancelled') {
            return 'cancelled';
        }

        if (today()->lte($this->ends_on)) {
            return 'current';
        }

        $graceEnd = $this->ends_on->copy()->addDays($graceDays);

        // La prórroga solo puede alargar la tolerancia, nunca acortarla
        if ($this->grace_until && $this->grace_until->gt($graceEnd)) {
            $graceEnd = $this->grace_until;
        }

        return today()->lte($graceEnd) ? 'grace' : 'expired';
    }
}