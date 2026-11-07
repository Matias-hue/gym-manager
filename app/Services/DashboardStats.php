<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Calcula todos los números de la pantalla de inicio.
 * Todas las consultas se filtran por gym_id.
 */
class DashboardStats
{
    private const SPINNING_SLOTS = [
        'tarde' => 'Turno tarde',
        'noche' => 'Turno noche',
    ];

    private const WEEKDAYS = [
        1 => 'lunes', 2 => 'martes', 3 => 'miércoles', 4 => 'jueves',
        5 => 'viernes', 6 => 'sábado', 7 => 'domingo',
    ];

    private const DUES_ROWS_PER_STATE = 8;

    private const RECENT_PAYMENTS = 5;

    private const VISITS_SHOWN = 8;

    public function handle(User $user): array
    {
        $gym = $user->gym;

        $states = $this->memberStates($gym->id, (int) $gym->grace_days);
        $counts = $states['counts'];
        $paymentsToday = $this->paymentsToday($gym->id);
        $attendance = $this->attendanceToday($gym->id);

        return [
            'summary' => [
                // Activo = socio con estado "activo" en su ficha (igual que la pantalla Socios)
                'active' => array_sum($counts),
                'current' => $counts['current'],
                'grace' => $counts['grace'],
                'expired' => $counts['expired'],
                // Socios activos sin cuota vigente: vencidos o que nunca pagaron
                'without_fee' => $counts['expired'] + $counts['none'],
                'receivable' => $counts['grace'] + $counts['expired'],
                'collected_today' => $paymentsToday['total'],
                'payments_today' => $paymentsToday['count'],
                'visits_today' => $attendance['count'],
            ],
            'dues' => $states['dues'],
            'payments' => $this->recentPayments($gym->id),
            'attendance' => $attendance,
            'spinning' => $this->spinning($gym->id),
        ];
    }

    /**
     * Estado de cada socio activo (al día, en gracia, vencido o sin pagos)
     * y las filas de la tabla "Quién tiene que pagar".
     */
    private function memberStates(int $gymId, int $graceDays): array
    {
        $members = Member::query()
            ->where('gym_id', $gymId)
            ->where('status', 'active')
            ->with('currentMembership.plan')
            ->get();

        $counts = ['current' => 0, 'grace' => 0, 'expired' => 0, 'none' => 0];
        $grace = collect();
        $expired = collect();

        foreach ($members as $member) {
            $membership = $member->currentMembership;
            $state = $membership ? $membership->state($graceDays) : 'none';
            $counts[$state] = ($counts[$state] ?? 0) + 1;

            if (! in_array($state, ['grace', 'expired'], true)) {
                continue;
            }

            // Evita una consulta extra por fila cuando el botón de prórroga lee al socio
            $membership->setRelation('member', $member);

            $row = [
                'member' => $member,
                'membership' => $membership,
                'state' => $state,
                'ends_on' => $membership->ends_on,
                'grace_end' => $membership->graceEndsOn($graceDays),
                'extended' => $membership->hasExtension($graceDays),
                'days_overdue' => (int) abs($membership->ends_on->diffInDays(today())),
            ];

            $state === 'grace' ? $grace->push($row) : $expired->push($row);
        }

        // En gracia: primero a quien se le acaba antes la tolerancia.
        // Vencidos: primero los que vencieron más recientemente (los más fáciles de recuperar).
        $graceRows = $grace->sortBy(fn ($row) => $row['grace_end']->timestamp)->values();
        $expiredRows = $expired->sortByDesc(fn ($row) => $row['ends_on']->timestamp)->values();
        $limit = self::DUES_ROWS_PER_STATE;

        return [
            'counts' => $counts,
            'dues' => [
                'rows' => $graceRows->take($limit)->concat($expiredRows->take($limit)),
                'all' => $grace->count() + $expired->count(),
                'grace' => $grace->count(),
                'expired' => $expired->count(),
                'shown_per_state' => $limit,
                'truncated' => $grace->count() > $limit || $expired->count() > $limit,
            ],
        ];
    }

    private function paymentsToday(int $gymId): array
    {
        $query = Payment::query()
            ->where('gym_id', $gymId)
            ->whereNull('voided_at')
            ->whereBetween('paid_at', [today()->startOfDay(), today()->endOfDay()]);

        return [
            'total' => (int) (clone $query)->sum('amount'),
            'count' => (clone $query)->count(),
        ];
    }

    private function recentPayments(int $gymId): Collection
    {
        return Payment::query()
            ->where('gym_id', $gymId)
            ->whereNull('voided_at')
            ->with(['member', 'membership.plan'])
            ->latest('paid_at')
            ->limit(self::RECENT_PAYMENTS)
            ->get();
    }

    /**
     * Ingresos de hoy, con las visitas que lleva cada socio en la semana.
     */
    private function attendanceToday(int $gymId): array
    {
        $all = Attendance::query()
            ->where('gym_id', $gymId)
            ->whereBetween('checked_in_at', [today()->startOfDay(), today()->endOfDay()])
            ->with('member.currentMembership.plan')
            ->latest('checked_in_at')
            ->get();

        $shown = $all->take(self::VISITS_SHOWN);

        // Una sola consulta para las visitas de la semana de todos los que se muestran
        $weekVisits = Attendance::query()
            ->where('gym_id', $gymId)
            ->whereIn('member_id', $shown->pluck('member_id')->unique())
            ->whereBetween('checked_in_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->selectRaw('member_id, COUNT(*) as total')
            ->groupBy('member_id')
            ->pluck('total', 'member_id');

        $items = $shown->map(function (Attendance $attendance) use ($weekVisits) {
            $plan = $attendance->member?->currentMembership?->plan;
            $limit = $plan?->visits_per_week;
            $visits = (int) $weekVisits->get($attendance->member_id, 0);

            return [
                'member' => $attendance->member,
                'plan' => $plan,
                'time' => $attendance->checked_in_at,
                'week_visits' => $visits,
                'limit' => $limit,
                'exceeded' => $limit !== null && $visits > $limit,
            ];
        });

        return [
            'count' => $all->count(),
            'items' => $items,
            'hidden' => max(0, $all->count() - $items->count()),
        ];
    }

    /**
     * Cupo por turno de los planes que tienen capacidad (spinning).
     */
    private function spinning(int $gymId): Collection
    {
        return Plan::query()
            ->where('gym_id', $gymId)
            ->where('active', true)
            ->whereNotNull('capacity_per_slot')
            ->orderBy('name')
            ->get()
            ->map(function (Plan $plan) {
                $capacity = (int) $plan->capacity_per_slot;
                $weekdays = $plan->allowed_weekdays ? array_map('intval', $plan->allowed_weekdays) : null;

                $slots = collect(self::SPINNING_SLOTS)
                    ->map(function (string $label, string $key) use ($plan, $capacity) {
                        $occupied = $plan->slotOccupancy($key);

                        return [
                            'label' => $label,
                            'occupied' => $occupied,
                            'capacity' => $capacity,
                            'percent' => $capacity > 0 ? min(100, (int) round($occupied / $capacity * 100)) : 0,
                            'full' => $capacity > 0 && $occupied >= $capacity,
                        ];
                    })
                    ->values();

                return [
                    'plan' => $plan,
                    'capacity' => $capacity,
                    'days' => $this->weekdaysLabel($weekdays),
                    'next_class' => $this->nextClass($weekdays),
                    'slots' => $slots,
                ];
            });
    }

    private function weekdaysLabel(?array $weekdays): string
    {
        if (! $weekdays) {
            return 'Todos los días';
        }

        sort($weekdays);
        $names = array_map(fn (int $day) => self::WEEKDAYS[$day] ?? '', $weekdays);
        $last = array_pop($names);

        return ucfirst($names ? implode(', ', $names).' y '.$last : $last);
    }

    private function nextClass(?array $weekdays): ?string
    {
        if (! $weekdays) {
            return null;
        }

        for ($i = 0; $i < 7; $i++) {
            $date = today()->addDays($i);

            if (in_array($date->isoWeekday(), $weekdays, true)) {
                return match ($i) {
                    0 => 'hoy',
                    1 => 'mañana',
                    default => $date->locale('es')->isoFormat('dddd D [de] MMMM'),
                };
            }
        }

        return null;
    }
}