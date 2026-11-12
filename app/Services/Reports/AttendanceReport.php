<?php

namespace App\Services\Reports;

use App\Models\Attendance;
use App\Models\Gym;
use App\Services\Reports\Concerns\UsesSqlExpressions;
use App\Services\Reports\Contracts\ExportableReport;
use App\Support\Delta;
use App\Support\Kpi;
use App\Support\WhatsappMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class AttendanceReport implements ExportableReport
{
    use UsesSqlExpressions;

    public function __construct(private readonly MembersReport $members) {}

    public function build(ReportFilters $f): array
    {
        $f = $f->withoutExtraFilters();

        $now = $this->totals($f);
        $previous = $this->totals($f->withPeriod($f->period->previous()));

        $weekdays = $this->byWeekday($f);
        $hours = $this->byHour($f);
        $peakDay = $this->peak($weekdays['labels'], $weekdays['values']);
        $peakHour = $this->peak($hours['labels'], $hours['values']);

        $snapshot = $this->members->snapshot($f->gym);

        return [
            'kpis' => [
                Kpi::make('Visitas', $now['visits'], 'number', Delta::percent($now['visits'], $previous['visits'])),
                Kpi::make('Socios que vinieron', $now['members'], 'number', Delta::percent($now['members'], $previous['members'])),
                Kpi::make('Promedio por día', $now['per_day'], 'decimal', null, false, 'Sobre los días transcurridos'),
                Kpi::make('Hora pico', $peakHour ? $peakHour['label'] : '—', 'text', null, false, $peakHour ? "{$peakHour['value']} visitas" : 'Sin visitas'),
                Kpi::make('Día más concurrido', $peakDay ? $peakDay['label'] : '—', 'text', null, false, $peakDay ? "{$peakDay['value']} visitas" : 'Sin visitas'),
            ],
            'charts' => [
                'trend'   => $this->trendChart($f),
                'weekday' => ChartData::make('bar', $weekdays['labels'], [ChartData::dataset('Visitas', $weekdays['values'])], 'number'),
                'hours'   => ChartData::make('bar', $hours['labels'], [ChartData::dataset('Visitas', $hours['values'])], 'number'),
                'methods' => $this->methodsChart($f),
            ],
            'top'      => $this->topMembers($f),
            'inactive' => $this->inactive($f->gym, $snapshot),
        ];
    }

    /** @return array{visits: int, members: int, per_day: float} */
    public function totals(ReportFilters $f): array
    {
        $row = $this->baseQuery($f)->toBase()
            ->selectRaw('COUNT(*) AS visits, COUNT(DISTINCT attendances.member_id) AS members')
            ->first();

        $visits = (int) $row->visits;

        return [
            'visits'  => $visits,
            'members' => (int) $row->members,
            'per_day' => round($visits / $f->period->elapsedDays(), 1),
        ];
    }

    /** Visitas en el tiempo (reutilizado por el resumen). */
    public function trendChart(ReportFilters $f): array
    {
        $bucket = $this->sqlBucket($f->period, 'attendances.checked_in_at');

        $rows = $this->baseQuery($f)->toBase()
            ->selectRaw("{$bucket} AS bucket, COUNT(*) AS n")
            ->groupBy('bucket')
            ->pluck('n', 'bucket');

        $labels = [];
        $values = [];
        foreach ($f->period->buckets() as $key => $label) {
            $labels[] = $label;
            $values[] = (int) ($rows[$key] ?? 0);
        }

        return ChartData::make('bar', $labels, [ChartData::dataset('Visitas', $values)], 'number');
    }

    // ---------------------------------------------------------------------
    // Consultas
    // ---------------------------------------------------------------------

    private function baseQuery(ReportFilters $f): Builder
    {
        return Attendance::query()
            ->where('attendances.gym_id', $f->gymId())
            ->whereBetween('attendances.checked_in_at', $f->period->range());
    }

    /** @return array{labels: list<string>, values: list<int>} lunes a domingo */
    private function byWeekday(ReportFilters $f): array
    {
        $weekday = $this->sqlIsoWeekday('attendances.checked_in_at');

        $rows = $this->baseQuery($f)->toBase()
            ->selectRaw("{$weekday} AS iso_weekday, COUNT(*) AS n")
            ->groupBy('iso_weekday')
            ->pluck('n', 'iso_weekday');

        $labels = [];
        $values = [];
        foreach (config('reports.weekdays', []) as $number => $label) {
            $labels[] = $label;
            $values[] = (int) ($rows[$number] ?? 0);
        }

        return compact('labels', 'values');
    }

    /** @return array{labels: list<string>, values: list<int>} solo desde la primera hasta la última hora con visitas */
    private function byHour(ReportFilters $f): array
    {
        $hour = $this->sqlHour('attendances.checked_in_at');

        $rows = $this->baseQuery($f)->toBase()
            ->selectRaw("{$hour} AS hour_of_day, COUNT(*) AS n")
            ->groupBy('hour_of_day')
            ->pluck('n', 'hour_of_day');

        if ($rows->isEmpty()) {
            return ['labels' => [], 'values' => []];
        }

        $hours = $rows->keys()->map(fn ($h) => (int) $h);
        $labels = [];
        $values = [];
        foreach (range($hours->min(), $hours->max()) as $h) {
            $labels[] = sprintf('%02d:00', $h);
            $values[] = (int) ($rows[$h] ?? 0);
        }

        return compact('labels', 'values');
    }

    private function methodsChart(ReportFilters $f): array
    {
        $rows = $this->baseQuery($f)->toBase()
            ->selectRaw('attendances.method, COUNT(*) AS n')
            ->groupBy('attendances.method')
            ->orderByDesc('n')
            ->get();

        return ChartData::make(
            'doughnut',
            $rows->map(fn ($r) => config("reports.attendance_methods.{$r->method}", $r->method))->all(),
            [ChartData::dataset('Visitas', $rows->pluck('n')->map(fn ($v) => (int) $v)->all())],
            'number',
        );
    }

    /** @return list<object{member_id: int, name: string, visits: int, last_visit: Carbon}> */
    private function topMembers(ReportFilters $f): array
    {
        return $this->baseQuery($f)->toBase()
            ->join('members', 'members.id', '=', 'attendances.member_id')
            ->selectRaw('members.id AS member_id, members.first_name, members.last_name, COUNT(*) AS visits, MAX(attendances.checked_in_at) AS last_visit')
            ->groupBy('members.id', 'members.first_name', 'members.last_name')
            ->orderByDesc('visits')
            ->orderBy('members.last_name')
            ->limit((int) config('reports.top_limit', 10))
            ->get()
            ->map(fn ($r) => (object) [
                'member_id'  => $r->member_id,
                'name'       => "{$r->last_name}, {$r->first_name}",
                'visits'     => (int) $r->visits,
                'last_visit' => Carbon::parse($r->last_visit),
            ])
            ->all();
    }

    /**
     * Socios con la cuota al día que hace tiempo no vienen (o nunca vinieron).
     * No depende del período elegido: mira hacia atrás desde hoy.
     *
     * @return array{days: int, total: int, rows: list<object>}
     */
    private function inactive(Gym $gym, Collection $snapshot): array
    {
        $days = (int) config('reports.inactive_days', 14);
        $limitDate = today()->subDays($days);

        $lastVisits = Attendance::query()
            ->where('gym_id', $gym->getKey())
            ->toBase()
            ->selectRaw('member_id, MAX(checked_in_at) AS last_visit')
            ->groupBy('member_id')
            ->pluck('last_visit', 'member_id');

        $rows = $snapshot
            ->where('state', 'current')
            ->map(function ($r) use ($lastVisits, $limitDate, $gym) {
                $member = $r->member;
                $last = isset($lastVisits[$member->id]) ? Carbon::parse($lastVisits[$member->id]) : null;

                if ($last && $last->startOfDay()->gte($limitDate)) {
                    return null; // vino hace poco
                }

                if (! $last && $member->joined_at && $member->joined_at->gte($limitDate)) {
                    return null; // recién se asoció, todavía no corresponde avisar
                }

                $message = WhatsappMessage::make('inactive', [
                    'name' => $member->first_name,
                    'gym'  => $gym->name,
                    'date' => $r->ends_on->format('d/m'),
                ]);

                return (object) [
                    'member_id'  => $member->id,
                    'name'       => "{$member->last_name}, {$member->first_name}",
                    'plan'       => $r->plan ?? 'Sin plan',
                    'last_visit' => $last,
                    'days_since' => $last ? (int) $last->copy()->startOfDay()->diffInDays(today()) : null,
                    'wa_url'     => method_exists($member, 'whatsappUrl') ? $member->whatsappUrl($message) : null,
                ];
            })
            ->filter()
            // Primero los que nunca vinieron, después los que hace más que no vienen.
            ->sortByDesc(fn ($r) => $r->days_since ?? PHP_INT_MAX)
            ->values();

        return [
            'days'  => $days,
            'total' => $rows->count(),
            'rows'  => $rows->take((int) config('reports.list_limit', 25))->all(),
        ];
    }

    /** @return ?array{label: string, value: int} */
    private function peak(array $labels, array $values): ?array
    {
        if ($values === [] || max($values) === 0) {
            return null;
        }

        $index = array_search(max($values), $values, true);

        return ['label' => $labels[$index], 'value' => $values[$index]];
    }

    // ---------------------------------------------------------------------
    // Exportación CSV
    // ---------------------------------------------------------------------

    public function exportName(): string
    {
        return 'visitas';
    }

    public function exportHeaders(): array
    {
        return ['Fecha y hora', 'Socio', 'DNI', 'Método', 'Registrado por'];
    }

    public function exportRows(ReportFilters $filters): iterable
    {
        $query = $this->baseQuery($filters)
            ->join('members', 'members.id', '=', 'attendances.member_id')
            ->leftJoin('users', 'users.id', '=', 'attendances.registered_by')
            ->select([
                'attendances.checked_in_at',
                'attendances.method',
                'members.first_name',
                'members.last_name',
                'members.document_number',
                'users.name AS registered_by_name',
            ])
            ->orderBy('attendances.checked_in_at')
            ->orderBy('attendances.id');

        foreach ($query->cursor() as $a) {
            yield [
                Carbon::parse($a->checked_in_at)->format('d/m/Y H:i'),
                "{$a->last_name}, {$a->first_name}",
                $a->document_number,
                config("reports.attendance_methods.{$a->method}", $a->method),
                $a->registered_by_name,
            ];
        }
    }
}
