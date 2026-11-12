<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use App\Models\Concerns\BelongsToGym;

class Membership extends Model
{
    use BelongsToGym;
    
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

    // Último día en que el socio todavía está "en gracia"
    public function graceEndsOn(int $graceDays = 14): Carbon
    {
        $graceEnd = $this->ends_on->copy()->addDays($graceDays);

        // La prórroga solo puede alargar la tolerancia, nunca acortarla
        if ($this->grace_until && $this->grace_until->gt($graceEnd)) {
            $graceEnd = $this->grace_until->copy();
        }

        return $graceEnd;
    }

    // true si la tolerancia llega más lejos por una prórroga dada a mano
    public function hasExtension(int $graceDays = 14): bool
    {
        return $this->grace_until !== null
            && $this->grace_until->gt($this->ends_on->copy()->addDays($graceDays));
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

        return today()->lte($this->graceEndsOn($graceDays)) ? 'grace' : 'expired';
    }
}