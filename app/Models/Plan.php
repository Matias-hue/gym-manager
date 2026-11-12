<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\BelongsToGym;

class Plan extends Model
{
    use BelongsToGym;
    
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = ['allowed_weekdays' => 'array'];

    // Bicicletas ocupadas en un turno (tarde o noche)
    public function slotOccupancy(string $slot): int
    {
        return Membership::where('plan_id', $this->id)
            ->where('status', 'active')
            ->whereDate('ends_on', '>=', today())
            ->where('time_slot', $slot)
            ->count();
    }
}