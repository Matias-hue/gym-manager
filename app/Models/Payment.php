<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
    'gym_id',
    'member_id',
    'membership_id',
    'amount',
    'method',
    'paid_at',
    'received_by',
    'notes',
];

    protected $guarded = [];
 
    protected $casts = [
        'paid_at' => 'datetime',
        'voided_at' => 'datetime',
    ];
 
    // withTrashed: si un socio se borra, sus pagos siguen mostrando el nombre
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class)->withTrashed();
    }
 
    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }
}
