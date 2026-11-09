<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Alta masiva de socios (pasar el cuaderno al sistema).
 *
 * Cada fila: first_name, last_name, phone?, plan_id?, time_slot?, ends_on?.
 * Si la fila tiene vencimiento (propio o el común) se crea también su membresía,
 * sin pago ni movimiento de caja: no entró plata ahora, solo se registra el estado.
 */
class ImportMembers
{
    public const SLOTS = ['tarde', 'noche'];

    /**
     * Devuelve:
     *  - created: socios creados
     *  - with_membership: cuántos de ellos quedaron con vencimiento
     *  - errors: [índice de fila => [mensajes]] (esas filas NO se guardan)
     *  - duplicates: [índice de fila => "Nombre Apellido"] ya existentes o repetidos
     *  - needs_confirmation: hay duplicados sin confirmar; no se guardó nada
     */
    public function handle(
        User $user,
        array $rows,
        ?int $defaultPlanId,
        ?string $defaultEndsOn,
        bool $confirmDuplicates = false,
    ): array {
        $gymId = $user->gym_id;

        $plans = Plan::where('gym_id', $gymId)->where('active', true)->get()->keyBy('id');
        $defaultEnds = filled($defaultEndsOn) ? $this->parseDate($defaultEndsOn) : null;

        $known = Member::where('gym_id', $gymId)->get(['first_name', 'last_name'])
            ->mapWithKeys(fn ($m) => [$this->nameKey($m->first_name, $m->last_name) => true])
            ->all();

        $ready = [];
        $errors = [];
        $duplicates = [];
        $seen = [];
        $occupancy = []; // [plan_id][turno] => lugares ocupados, contando las filas de este lote

        foreach ($rows as $i => $row) {
            $first = $this->clean($row['first_name'] ?? null);
            $last = $this->clean($row['last_name'] ?? null);
            $phoneText = trim((string) ($row['phone'] ?? ''));
            $endsText = trim((string) ($row['ends_on'] ?? ''));

            // Fila vacía: se ignora sin molestar
            if ($first === '' && $last === '' && $phoneText === '' && $endsText === '') {
                continue;
            }

            $messages = [];

            if ($first === '') {
                $messages[] = 'Falta el nombre.';
            }
            if ($last === '') {
                $messages[] = 'Falta el apellido.';
            }

            $phone = null;
            if ($phoneText !== '') {
                $digits = preg_replace('/\D+/', '', $phoneText);
                if (strlen($digits) < 8 || strlen($digits) > 15) {
                    $messages[] = 'El celular debe tener entre 8 y 15 números.';
                } else {
                    $phone = $digits;
                }
            }

            // Vencimiento: el de la fila manda sobre el común
            $ends = $defaultEnds;
            if ($endsText !== '') {
                $ends = $this->parseDate($endsText);
                if (! $ends) {
                    $messages[] = "La fecha \"{$endsText}\" no es válida. Escribila como 15/11 o 15/11/2026.";
                }
            }

            $plan = null;
            $slot = null;
            $occupies = false;

            if ($ends) {
                $planId = filled($row['plan_id'] ?? null) ? (int) $row['plan_id'] : $defaultPlanId;
                $plan = $planId ? $plans->get($planId) : null;

                if (! $plan) {
                    $messages[] = 'Elegí un plan para cargar el vencimiento.';
                } elseif ($plan->capacity_per_slot) {
                    $slot = $row['time_slot'] ?? null;

                    if (! in_array($slot, self::SLOTS, true)) {
                        $messages[] = 'Elegí el turno (tarde o noche).';
                    } elseif ($ends->gte(today())) {
                        // Solo ocupa lugar si todavía está vigente
                        $occupies = true;
                        $occupancy[$plan->id][$slot] ??= $plan->slotOccupancy($slot);

                        if ($occupancy[$plan->id][$slot] >= $plan->capacity_per_slot) {
                            $messages[] = "El turno {$slot} de {$plan->name} está lleno ({$plan->capacity_per_slot} lugares).";
                        }
                    }
                }
            }

            if ($messages) {
                $errors[$i] = $messages;
                continue;
            }

            if ($occupies) {
                $occupancy[$plan->id][$slot]++;
            }

            $key = $this->nameKey($first, $last);
            if (isset($known[$key]) || isset($seen[$key])) {
                $duplicates[$i] = "{$first} {$last}";
            }
            $seen[$key] = true;

            $ready[$i] = compact('first', 'last', 'phone', 'plan', 'slot', 'ends');
        }

        // Posibles duplicados sin confirmar: no se guarda nada hasta que decidan
        if ($duplicates && ! $confirmDuplicates) {
            return [
                'created' => 0,
                'with_membership' => 0,
                'errors' => $errors,
                'duplicates' => $duplicates,
                'needs_confirmation' => true,
            ];
        }

        $created = 0;
        $withMembership = 0;

        DB::transaction(function () use ($ready, $gymId, &$created, &$withMembership) {
            foreach ($ready as $r) {
                $member = Member::create([
                    'gym_id' => $gymId,
                    'first_name' => $r['first'],
                    'last_name' => $r['last'],
                    'phone' => $r['phone'],
                    'status' => 'active',
                    'joined_at' => today(),
                ]);
                $created++;

                if ($r['plan']) {
                    Membership::create([
                        'member_id' => $member->id,
                        'plan_id' => $r['plan']->id,
                        'time_slot' => $r['slot'],
                        'starts_on' => $r['ends']->copy()->subMonthNoOverflow(),
                        'ends_on' => $r['ends'],
                        'price_charged' => $r['plan']->price,
                        'notes' => 'Cargado del cuaderno',
                    ]);
                    $withMembership++;
                }
            }
        });

        return [
            'created' => $created,
            'with_membership' => $withMembership,
            'errors' => $errors,
            'duplicates' => [],
            'needs_confirmation' => false,
        ];
    }

    /**
     * Acepta 15/11, 15-11, 15.11, 15/11/26 y 15/11/2026.
     * Sin año, elige el año que deja la fecha más cerca de hoy.
     */
    public function parseDate(string $text): ?Carbon
    {
        if (! preg_match('/^\s*(\d{1,2})\s*[\/\-.]\s*(\d{1,2})(?:\s*[\/\-.]\s*(\d{2}|\d{4}))?\s*$/', $text, $m)) {
            return null;
        }

        $day = (int) $m[1];
        $month = (int) $m[2];

        if (! empty($m[3])) {
            $year = (int) $m[3];
            if ($year < 100) {
                $year += 2000;
            }

            return checkdate($month, $day, $year) ? Carbon::create($year, $month, $day)->startOfDay() : null;
        }

        $today = today();
        $best = null;

        foreach ([-1, 0, 1] as $offset) {
            $year = $today->year + $offset;

            if (! checkdate($month, $day, $year)) {
                continue;
            }

            $candidate = Carbon::create($year, $month, $day)->startOfDay();

            if ($best === null || abs($candidate->diffInDays($today, false)) < abs($best->diffInDays($today, false))) {
                $best = $candidate;
            }
        }

        return $best;
    }

    private function clean(mixed $value): string
    {
        return preg_replace('/\s+/u', ' ', trim((string) $value));
    }

    // "Rocío Giménez" y "rocio gimenez" cuentan como el mismo nombre
    private function nameKey(string $first, string $last): string
    {
        return Str::lower(Str::ascii("{$first} {$last}"));
    }
}
