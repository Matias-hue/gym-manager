<?php

namespace Database\Seeders;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $gym = Gym::create([
            'name' => 'Gimnasio piloto',
            'timezone' => 'America/Asuncion',
            'currency' => 'PYG',
            'grace_days' => 14,
        ]);

        User::create([
            'gym_id' => $gym->id,
            'name' => 'Dueño',
            'email' => 'dueno@ejemplo.com',
            'password' => Hash::make('cambiar-esta-clave'),
            'role' => 'owner',
        ]);

        User::create([
            'gym_id' => $gym->id,
            'name' => 'Recepción',
            'email' => 'recepcion@ejemplo.com',
            'password' => Hash::make('cambiar-esta-clave'),
            'role' => 'reception',
        ]);
    }
}