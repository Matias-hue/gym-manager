<?php

namespace Database\Seeders;

use App\Models\Gym;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $gym = Gym::create([
            'name' => 'Gimnasio piloto',
            'timezone' => 'America/Argentina/Buenos_Aires',
            'currency' => 'ARS',
            'grace_days' => 14,
        ]);

        $owner = User::create([
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

        // Planes reales del gimnasio piloto
        $libre = Plan::create([
            'gym_id' => $gym->id,
            'name' => 'Musculación libre',
            'price' => 25000,
        ]);

        $tresDias = Plan::create([
            'gym_id' => $gym->id,
            'name' => 'Musculación 3 días',
            'price' => 20000,
            'visits_per_week' => 3,
        ]);

        $spinning = Plan::create([
            'gym_id' => $gym->id,
            'name' => 'Spinning',
            'price' => 22000, // PRECIO A CONFIRMAR con el dueño
            'visits_per_week' => 3,
            'allowed_weekdays' => [1, 3, 5],
            'capacity_per_slot' => 20,
        ]);

        // Socios de prueba: algunos con DNI y otros sin él, en distintos estados.
        // [nombre, apellido, teléfono, documento, plan, días hasta el vencimiento, turno]
        $demo = [
            ['Martina', 'Gómez', '3764000001', null, $libre, 12, null],
            ['Lucas', 'Benítez', '3764000002', '38456789', $tresDias, 5, null],
            ['Camila', 'Ortiz', '3764000006', null, $spinning, 3, 'noche'],
            ['Rocío', 'Giménez', '3764000003', null, $libre, -6, null],       // en gracia
            ['Gonzalo', 'Peralta', null, null, $tresDias, -9, null],           // en gracia, sin teléfono
            ['Valentina', 'Ríos', '3764000005', '43560217', $spinning, -30, 'noche'], // vencido
            ['Joaquín', 'Villalba', '3764000007', null, $tresDias, null, null], // todavía no pagó
        ];

        foreach ($demo as $i => [$first, $last, $phone, $document, $plan, $daysLeft, $slot]) {
            $member = Member::create([
                'gym_id' => $gym->id,
                'first_name' => $first,
                'last_name' => $last,
                'phone' => $phone,
                'document_number' => $document,
                'joined_at' => today()->subDays(60),
            ]);

            if ($daysLeft === null) {
                continue;
            }

            $ends = today()->addDays($daysLeft);
            $starts = $ends->copy()->subMonthNoOverflow();

            $membership = Membership::create([
                'member_id' => $member->id,
                'plan_id' => $plan->id,
                'time_slot' => $slot,
                'starts_on' => $starts,
                'ends_on' => $ends,
                'price_charged' => $plan->price,
            ]);

            Payment::create([
                'gym_id' => $gym->id,
                'member_id' => $member->id,
                'membership_id' => $membership->id,
                'amount' => $plan->price,
                'method' => $i % 2 === 0 ? 'cash' : 'transfer',
                'paid_at' => $starts->copy()->setTime(18, 30),
                'received_by' => $owner->id,
            ]);
        }
    }
}
