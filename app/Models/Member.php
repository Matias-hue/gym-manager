<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\Concerns\BelongsToGym;

class Member extends Model
{
    use BelongsToGym;
    
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = ['birth_date' => 'date', 'joined_at' => 'date'];

    protected static function booted(): void
    {
        static::creating(function (Member $member) {
            $member->qr_code ??= (string) Str::uuid();
        });
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

    /**
     * Busca por nombre, apellido, teléfono o documento (si lo tiene).
     * Cada palabra tiene que aparecer en algún campo: "juan per" encuentra a Juan Pérez.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $words = preg_split('/\s+/', trim((string) $term), -1, PREG_SPLIT_NO_EMPTY);

        foreach ($words as $word) {
            $like = '%'.addcslashes($word, '%_\\').'%';

            $query->where(function (Builder $q) use ($like) {
                $q->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('document_number', 'like', $like);
            });
        }

        return $query;
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
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

    // Enlace de WhatsApp con el mensaje escrito. Celulares argentinos: 549 + código de área + número.
    public function whatsappUrl(string $message): ?string
    {
        $digits = preg_replace('/\D+/', '', $this->phone ?? '');

        if ($digits === '') {
            return null;
        }

        if (! str_starts_with($digits, '54')) {
            $digits = '549'.ltrim($digits, '0');
        } elseif (! str_starts_with($digits, '549')) {
            $digits = '549'.substr($digits, 2);
        }

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
    }
}
