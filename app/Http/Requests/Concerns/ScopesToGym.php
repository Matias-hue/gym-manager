<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

trait ScopesToGym
{
    protected function gymId(): int
    {
        return $this->user()->gym_id;
    }

    protected function existsInGym(string $table, string $column = 'id'): Exists
    {
        return Rule::exists($table, $column)->where('gym_id', $this->gymId());
    }

    protected function uniqueInGym(string $table, string $column): Unique
    {
        return Rule::unique($table, $column)->where('gym_id', $this->gymId());
    }
}