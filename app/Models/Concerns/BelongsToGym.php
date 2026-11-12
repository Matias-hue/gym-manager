<?php

namespace App\Models\Concerns;

use App\Models\Gym;
use App\Models\Scopes\GymScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToGym
{
    protected static function bootBelongsToGym(): void
    {
        static::addGlobalScope(new GymScope);

        static::creating(function ($model) {
            if (empty($model->gym_id) && Auth::hasUser()) {
                $model->gym_id = Auth::user()->gym_id;
            }
        });
    }

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }
}