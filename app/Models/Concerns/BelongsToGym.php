<?php

namespace App\Models\Concerns;

use App\Models\Gym;
use App\Models\Scopes\GymScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use LogicException;

trait BelongsToGym
{
    protected static function bootBelongsToGym(): void
    {
        static::addGlobalScope(new GymScope);

        // Al crear, el registro hereda el gimnasio del usuario si no se indicó uno
        static::creating(function ($model) {
            if (empty($model->gym_id) && Auth::hasUser()) {
                $model->gym_id = Auth::user()->gym_id;
            }
        });

        // Un registro nunca puede pasar de un gimnasio a otro, ni por error ni por un
        // campo "gym_id" colado en un formulario.
        static::updating(function ($model) {
            if ($model->isDirty('gym_id') && $model->getOriginal('gym_id') !== null) {
                throw new LogicException('No se puede mover un registro a otro gimnasio.');
            }
        });
    }

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }
}