<?php

namespace App\Services\Reports;

use App\Support\Delta;
use App\Support\Kpi;

/**
 * Resumen general: junta lo principal de los demás reportes en una sola vista.
 */
final class SummaryReport
{
    public function __construct(
        private readonly IncomeReport $income,
        private readonly AttendanceReport $attendance,
        private readonly MembersReport $members,
    ) {}

    public function build(ReportFilters $f): array
    {
        $f = $f->withoutExtraFilters();
        $previous = $f->withPeriod($f->period->previous());

        $incomeNow = $this->income->totals($f);
        $incomePrev = $this->income->totals($previous);
        $visitsNow = $this->attendance->totals($f);
        $visitsPrev = $this->attendance->totals($previous);
        $newNow = $this->members->newMembersCount($f);
        $newPrev = $this->members->newMembersCount($previous);

        $snapshot = $this->members->snapshot($f->gym);
        $states = $this->members->stateCounts($snapshot);
        $active = array_sum($states);

        $expiringDays = (int) config('reports.expiring_days', 7);
        $expiringSoon = $snapshot
            ->where('state', 'current')
            ->filter(fn ($r) => $r->days_left <= $expiringDays)
            ->count();

        return [
            'kpis' => [
                Kpi::make('Ingresos', $incomeNow['total'], 'money', Delta::percent($incomeNow['total'], $incomePrev['total'])),
                Kpi::make('Cobros registrados', $incomeNow['count'], 'number', Delta::percent($incomeNow['count'], $incomePrev['count'])),
                Kpi::make('Ticket promedio', $incomeNow['average'], 'money', Delta::percent($incomeNow['average'], $incomePrev['average'])),
                Kpi::make('Socios nuevos', $newNow, 'number', Delta::percent($newNow, $newPrev)),
                Kpi::make('Visitas', $visitsNow['visits'], 'number', Delta::percent($visitsNow['visits'], $visitsPrev['visits'])),
                Kpi::make('Socios con cuota al día', $states['current'], 'number', null, false, "de {$active} socios activos"),
                Kpi::make('Vencen en '.$expiringDays.' días', $expiringSoon, 'number', null, false, 'Hoy, sin importar el período'),
                Kpi::make('Con cuota vencida', $states['grace'] + $states['expired'], 'number', null, true, "{$states['grace']} en gracia"),
            ],
            'charts' => [
                'income' => $this->income->trendChart($f),
                'visits' => $this->attendance->trendChart($f),
                'states' => $this->members->statesChart($states),
            ],
            'granularity_label' => $f->period->granularity() === 'month' ? 'Agrupado por mes' : 'Agrupado por día',
        ];
    }
}
