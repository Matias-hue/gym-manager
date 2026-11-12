<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class GymScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Sin usuario (artisan, seeders) no filtra; con usuario, siempre filtra
        if (Auth::hasUser()) {
            $builder->where($model->getTable().'.gym_id', Auth::user()->gym_id);
        }
    }
}