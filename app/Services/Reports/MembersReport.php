<?php

namespace App\Services\Reports;

use App\Models\Gym;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Plan;
use App\Services\Reports\Concerns\UsesSqlExpressions;
use App\Services\Reports\Contracts\ExportableReport;
use App\Support\Delta;
use App\Support\Kpi;
use App\Support\WhatsappMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Estado de los socios. El estado de la cuota se calcula con Membership::state(),
 * la misma regla que usa el resto del sistema (nunca se guarda).
 */
final class MembersReport implements ExportableReport
{
    use UsesSqlExpressions;

    public function build(ReportFilters $f): array
    {
        $f = $f->withoutExtraFilters();

        $snapshot = $this->snapshot($f->gym);
        $states = $this->stateCounts($snapshot);
        $retention = $this->retention($f);
        $newNow = $this->newMembersCount($f);
        $newPrev = $this->newMembersCount($f->withPeriod($f->period->previous()));

        $statusCounts = Member::query()
            ->where('gym_id', $f->gymId())
            ->selectRaw('status, COUNT(*) AS n')
            ->groupBy('status')
            ->pluck('n', 'status');

        $active = array_sum($states);
        $notActive = (int) $statusCounts->except('active')->sum();
        $pct = fn (int $n) => $active > 0 ? number_format($n / $active * 100, 0).' % de los activos' : null;

        $expiringDays = (int) config('reports.expiring_days', 7);

        $expiring = $snapshot
            ->where('state', 'current')
            ->filter(fn ($r) => $r->days_left <= $expiringDays)
            ->sortBy(fn ($r) => $r->ends_on->timestamp)
            ->values();

        $overdue = $snapshot
            ->whereIn('state', ['grace', 'expired'])
            ->sortByDesc(fn ($r) => $r->ends_on->timestamp)
            ->values();

        $limit = (int) config('reports.list_limit', 25);

        return [
            'kpis' => [
                Kpi::make('Socios activos', $active, 'number', null, false, $notActive > 0 ? "{$notActive} inactivos o suspendidos" : null),
                Kpi::make('Cuota al día', $states['current'], 'number', null, false, $pct($states['current'])),
                Kpi::make('En gracia', $states['grace'], 'number', null, false, $pct($states['grace'])),
                Kpi::make('Cuota vencida', $states['expired'], 'number', null, true, $states['none'] > 0 ? "{$states['none']} sin cuota nunca pagada" : $pct($states['expired'])),
                Kpi::make('Socios nuevos', $newNow, 'number', Delta::percent($newNow, $newPrev)),
                Kpi::make(
                    'Renovación',
                    $retention['rate'],
                    'percent',
                    null,
                    false,
                    $retention['ended'] > 0
                        ? "{$retention['renewed']} de {$retention['ended']} cuotas vencidas renovaron"
                        : 'Sin cuotas vencidas en el período',
                ),
            ],
            'charts' => [
                'states'      => $this->statesChart($states),
                'new_members' => $this->newMembersChart($f),
                'plans'       => $this->plansChart($snapshot),
            ],
            'occupancy' => $this->occupancy($f->gym),
            'expiring'  => [
                'days'  => $expiringDays,
                'total' => $expiring->count(),
                'rows'  => $expiring->take($limit)->map(fn ($r) => $this->contactRow($r, $f->gym, 'expiring'))->all(),
            ],
            'overdue' => [
                'total' => $overdue->count(),
                'rows'  => $overdue->take($limit)->map(fn ($r) => $this->contactRow($r, $f->gym, 'overdue'))->all(),
            ],
            'list_limit' => $limit,
        ];
    }

    // ---------------------------------------------------------------------
    // Estado de cada socio (compartido con otros reportes)
    // ---------------------------------------------------------------------

    /**
     * Un registro por socio con su cuota actual ya evaluada.
     *
     * @return Collection<int, object{member: Member, membership: ?Membership, state: string, ends_on: ?Carbon, days_left: ?int, plan: ?string}>
     */
    public function snapshot(Gym $gym, bool $onlyActive = true): Collection
    {
        $graceDays = (int) ($gym->grace_days ?? 14);

        $members = Member::query()
            ->where('gym_id', $gym->getKey())
            ->when($onlyActive, fn ($q) => $q->where('status', 'active'))
            ->with(['currentMembership.plan' => fn ($q) => $q->withTrashed()])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return $members->map(function (Member $member) use ($graceDays) {
            $membership = $member->currentMembership;

            return (object) [
                'member'     => $member,
                'membership' => $membership,
                'state'      => $membership ? $membership->state($graceDays) : 'none',
                'ends_on'    => $membership?->ends_on,
                'days_left'  => $membership ? (int) today()->diffInDays($membership->ends_on, false) : null,
                'plan'       => $membership?->plan?->name,
            ];
        });
    }

    /** @return array{current: int, grace: int, expired: int, none: int} */
    public function stateCounts(Collection $snapshot): array
    {
        $counts = $snapshot->countBy('state');

        return [
            'current' => (int) ($counts['current'] ?? 0),
            'grace'   => (int) ($counts['grace'] ?? 0),
            'expired' => (int) ($counts['expired'] ?? 0),
            'none'    => (int) ($counts['none'] ?? 0),
        ];
    }

    public function statesChart(array $states): array
    {
        $labels = [];
        foreach (array_keys($states) as $key) {
            $labels[] = config("reports.states.{$key}", $key);
        }

        return ChartData::make(
            'doughnut',
            $labels,
            [ChartData::dataset('Socios', array_values($states))],
            'number',
            ['tones' => ['success', 'warning', 'danger', 'neutral']],
        );
    }

    public function newMembersCount(ReportFilters $f): int
    {
        return Member::query()
            ->where('gym_id', $f->gymId())
            ->whereBetween('joined_at', $f->period->dateRange())
            ->count();
    }

    // ---------------------------------------------------------------------
    // Piezas del reporte
    // ---------------------------------------------------------------------

    private function newMembersChart(ReportFilters $f): array
    {
        $bucket = $this->sqlBucket($f->period, 'joined_at');

        $rows = Member::query()
            ->where('gym_id', $f->gymId())
            ->whereBetween('joined_at', $f->period->dateRange())
            ->toBase()
            ->selectRaw("{$bucket} AS bucket, COUNT(*) AS n")
            ->groupBy('bucket')
            ->pluck('n', 'bucket');

        $labels = [];
        $values = [];
        foreach ($f->period->buckets() as $key => $label) {
            $labels[] = $label;
            $values[] = (int) ($rows[$key] ?? 0);
        }

        return ChartData::make('bar', $labels, [ChartData::dataset('Altas', $values)], 'number');
    }

    private function plansChart(Collection $snapshot): array
    {
        $byPlan = $snapshot
            ->where('state', 'current')
            ->countBy(fn ($r) => $r->plan ?? 'Sin plan')
            ->sortDesc();

        return ChartData::make(
            'bar',
            $byPlan->keys()->all(),
            [ChartData::dataset('Socios con cuota al día', $byPlan->values()->all())],
            'number',
            ['horizontal' => true],
        );
    }

    /**
     * Cupos ocupados por turno en los planes con capacidad (spinning).
     *
     * @return list<array{plan: string, slot: string, used: int, capacity: int, pct: int}>
     */
    private function occupancy(Gym $gym): array
    {
        $plans = Plan::query()
            ->where('gym_id', $gym->getKey())
            ->where('active', true)
            ->whereNotNull('capacity_per_slot')
            ->get(['id', 'name', 'capacity_per_slot']);

        if ($plans->isEmpty()) {
            return [];
        }

        $used = Membership::query()
            ->where('status', 'active')
            ->whereDate('ends_on', '>=', today())
            ->whereIn('plan_id', $plans->pluck('id'))
            ->toBase()
            ->selectRaw('plan_id, time_slot, COUNT(*) AS n')
            ->groupBy('plan_id', 'time_slot')
            ->get();

        $rows = [];
        foreach ($plans as $plan) {
            foreach (config('reports.slots', []) as $slot => $label) {
                $match = $used->first(fn ($u) => (int) $u->plan_id === (int) $plan->id && $u->time_slot === $slot);
                $n = $match ? (int) $match->n : 0;
                $capacity = (int) $plan->capacity_per_slot;

                $rows[] = [
                    'plan'     => $plan->name,
                    'slot'     => $label,
                    'used'     => $n,
                    'capacity' => $capacity,
                    'pct'      => $capacity > 0 ? (int) round($n / $capacity * 100) : 0,
                ];
            }
        }

        return $rows;
    }

    /**
     * De las cuotas que vencieron en el período (y ya pasaron), cuántos socios
     * tienen una cuota posterior.
     *
     * @return array{ended: int, renewed: int, rate: ?float}
     */
    private function retention(ReportFilters $f): array
    {
        $to = $f->period->to->copy()->startOfDay();
        if ($to->gt(today())) {
            $to = today();
        }

        $ending = Membership::query()
            ->join('members', 'members.id', '=', 'memberships.member_id')
            ->where('members.gym_id', $f->gymId())
            ->whereNull('members.deleted_at')
            ->where('memberships.status', 'active')
            ->whereBetween('memberships.ends_on', [$f->period->from->toDateString(), $to->toDateString()])
            ->get(['memberships.member_id', 'memberships.ends_on']);

        if ($ending->isEmpty()) {
            return ['ended' => 0, 'renewed' => 0, 'rate' => null];
        }

        $lastEndingByMember = $ending->groupBy('member_id')->map(fn ($g) => $g->max('ends_on'));

        $latest = Membership::query()
            ->where('status', 'active')
            ->whereIn('member_id', $lastEndingByMember->keys())
            ->toBase()
            ->selectRaw('member_id, MAX(ends_on) AS last_end')
            ->groupBy('member_id')
            ->pluck('last_end', 'member_id');

        $renewed = $lastEndingByMember
            ->filter(fn ($endedOn, $memberId) => isset($latest[$memberId]) && Carbon::parse($latest[$memberId])->gt($endedOn))
            ->count();

        $ended = $lastEndingByMember->count();

        return [
            'ended'   => $ended,
            'renewed' => $renewed,
            'rate'    => round($renewed / $ended * 100, 1),
        ];
    }

    /** Fila para las listas de contacto (vencen pronto / vencidos). */
    private function contactRow(object $r, Gym $gym, string $messageKey): object
    {
        $message = WhatsappMessage::make($messageKey, [
            'name' => $r->member->first_name,
            'gym'  => $gym->name,
            'date' => $r->ends_on->format('d/m'),
        ]);

        return (object) [
            'member_id' => $r->member->id,
            'name'      => "{$r->member->last_name}, {$r->member->first_name}",
            'phone'     => $r->member->phone,
            'plan'      => $r->plan ?? 'Sin plan',
            'state'     => $r->state,
            'ends_on'   => $r->ends_on,
            'days'      => abs((int) $r->days_left),
            'days_left' => (int) $r->days_left,
            'wa_url'    => method_exists($r->member, 'whatsappUrl') ? $r->member->whatsappUrl($message) : null,
        ];
    }

    // ---------------------------------------------------------------------
    // Exportación CSV
    // ---------------------------------------------------------------------

    public function exportName(): string
    {
        return 'socios';
    }

    public function exportHeaders(): array
    {
        return [
            'Apellido', 'Nombre', 'DNI', 'Teléfono', 'Estado del socio',
            'Estado de la cuota', 'Plan', 'Turno', 'Vence', 'Días restantes', 'Alta',
        ];
    }

    public function exportRows(ReportFilters $filters): iterable
    {
        foreach ($this->snapshot($filters->gym, false) as $r) {
            yield [
                $r->member->last_name,
                $r->member->first_name,
                $r->member->document_number,
                $r->member->phone,
                $r->member->status,
                config("reports.states.{$r->state}", $r->state),
                $r->plan,
                $r->membership?->time_slot,
                $r->ends_on?->format('d/m/Y'),
                $r->days_left,
                $r->member->joined_at?->format('d/m/Y'),
            ];
        }
    }
}
