<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
