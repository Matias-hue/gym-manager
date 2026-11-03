<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Member extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = ['birth_date' => 'date', 'joined_at' => 'date'];

    protected static function booted(): void
    {
        static::creating(function (Member $member) {
            $member->qr_code ??= (string) Str::uuid();
        });
    }

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    // La membresía activa con el vencimiento más lejano
    public function currentMembership(): HasOne
    {
        return $this->hasOne(Membership::class)->ofMany(
            ['ends_on' => 'max'],
            fn ($query) => $query->where('status', 'active')
        );
    }

    // current (al día) | grace (en gracia) | expired (vencido) | none (nunca pagó)
    public function state(): string
    {
        $membership = $this->currentMembership;

        return $membership ? $membership->state($this->gym->grace_days) : 'none';
    }

    public function visitsThisWeek(): int
    {
        return $this->attendances()
            ->whereBetween('checked_in_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
    }

    public function whatsappUrl(string $message): ?string
{
    $digits = preg_replace('/\D+/', '', $this->phone ?? '');

    if ($digits === '') {
        return null;
    }

    // Celulares argentinos en WhatsApp: 549 + código de área + número
    if (! str_starts_with($digits, '54')) {
        $digits = '549'.ltrim($digits, '0');
    } elseif (! str_starts_with($digits, '549')) {
        $digits = '549'.substr($digits, 2);
    }

    return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
}
}