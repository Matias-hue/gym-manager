<?php

namespace App\Console\Commands;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateGym extends Command
{
    protected $signature = 'gym:create {name} {owner_name} {owner_email}';
    protected $description = 'Crea un gimnasio nuevo con su usuario dueño';

    public function handle(): int
    {
        $password = Str::password(12);

        DB::transaction(function () use ($password) {
            $gym = Gym::create(['name' => $this->argument('name')]);

            User::create([
                'gym_id' => $gym->id,
                'name' => $this->argument('owner_name'),
                'email' => $this->argument('owner_email'),
                'password' => Hash::make($password),
                'role' => 'owner',
            ]);
        });

        $this->info("Gimnasio creado. Clave inicial del dueño: {$password}");

        return self::SUCCESS;
    }
}