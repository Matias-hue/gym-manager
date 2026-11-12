<?php

namespace App\Services\Reports;

use App\Models\Membership;
use App\Models\Payment;
use App\Services\Reports\Concerns\UsesSqlExpressions;
use App\Services\Reports\Contracts\ExportableReport;
use App\Support\Delta;
use App\Support\Kpi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

/**
 * Cobros del período. Los pagos anulados (voided_at) nunca suman a los totales:
 * solo aparecen marcados en el detalle y en el CSV.
 */
final class IncomeReport implements ExportableReport
{
    use UsesSqlExpressions;

    public function build(ReportFilters $f): array
    {
        $current = $this->totals($f);
        $previous = $this->totals($f->withPeriod($f->period->previous()));

        $series = $this->series($f);
        $best = collect($series)->sortByDesc('total')->first();
        $isMonthly = $f->period->granularity() === 'month';

        return [
            'kpis' => [
                Kpi::make('Ingresos', $current['total'], 'money', Delta::percent($current['total'], $previous['total'])),
                Kpi::make('Cobros registrados', $current['count'], 'number', Delta::percent($current['count'], $previous['count'])),
                Kpi::make('Ticket promedio', $current['average'], 'money', Delta::percent($current['average'], $previous['average'])),
                Kpi::make(
                    $isMonthly ? 'Mejor mes' : 'Mejor día',
                    $best && $best['total'] > 0 ? $best['total'] : 0,
                    'money',
                    null,
                    false,
                    $best && $best['total'] > 0 ? $best['label'] : 'Sin cobros',
                ),
            ],
            'granularity_label' => $isMonthly ? 'Agrupado por mes' : 'Agrupado por día',
            'charts' => [
                'trend'   => $this->trendChart($f, $series),
                'methods' => $this->methodsChart($f),
                'plans'   => $this->plansChart($f),
            ],
            'staff'    => $this->byStaff($f),
            'voided'   => $this->voided($f),
            'payments' => $this->paginate($f),
        ];
    }

    /** @return array{total: int, count: int, average: int} */
    public function totals(ReportFilters $f): array
    {
        $row = $this->baseQuery($f)->toBase()
            ->selectRaw('COALESCE(SUM(payments.amount), 0) AS total, COUNT(*) AS n')
            ->first();

        $total = (int) $row->total;
        $count = (int) $row->n;

        return [
            'total'   => $total,
            'count'   => $count,
            'average' => $count > 0 ? (int) round($total / $count) : 0,
        ];
    }

    /** Gráfico de ingresos en el tiempo (reutilizado por el resumen). */
    public function trendChart(ReportFilters $f, ?array $series = null): array
    {
        $series ??= $this->series($f);
        $type = $f->period->granularity() === 'month' ? 'bar' : 'line';

        return ChartData::make(
            $type,
            array_column($series, 'label'),
            [ChartData::dataset('Ingresos', array_column($series, 'total'))],
            'money',
        );
    }

    // ---------------------------------------------------------------------
    // Consultas
    // ---------------------------------------------------------------------

    /** Pagos del gimnasio en el período, con los filtros de método y plan. */
    private function baseQuery(ReportFilters $f, bool $withVoided = false): Builder
    {
        $query = Payment::query()
            ->where('payments.gym_id', $f->gymId())
            ->whereBetween('payments.paid_at', $f->period->range());

        if (! $withVoided) {
            $query->whereNull('payments.voided_at');
        }

        if ($f->method) {
            $query->where('payments.method', $f->method);
        }

        if ($f->planId) {
            $query->whereIn(
                'payments.membership_id',
                Membership::query()->select('id')->where('plan_id', $f->planId),
            );
        }

        return $query;
    }

    /** @return array<string, array{label: string, total: int, count: int}> */
    private function series(ReportFilters $f): array
    {
        $bucket = $this->sqlBucket($f->period, 'payments.paid_at');

        $rows = $this->baseQuery($f)->toBase()
            ->selectRaw("{$bucket} AS bucket, SUM(payments.amount) AS total, COUNT(*) AS n")
            ->groupBy('bucket')
            ->get()
            ->keyBy('bucket');

        $series = [];
        foreach ($f->period->buckets() as $key => $label) {
            $row = $rows->get($key);
            $series[$key] = [
                'label' => $label,
                'total' => (int) ($row->total ?? 0),
                'count' => (int) ($row->n ?? 0),
            ];
        }

        return $series;
    }

    private function methodsChart(ReportFilters $f): array
    {
        $rows = $this->baseQuery($f)->toBase()
            ->selectRaw('payments.method, SUM(payments.amount) AS total')
            ->groupBy('payments.method')
            ->orderByDesc('total')
            ->get();

        return ChartData::make(
            'doughnut',
            $rows->map(fn ($r) => config("reports.methods.{$r->method}", $r->method))->all(),
            [ChartData::dataset('Ingresos', $rows->pluck('total')->map(fn ($v) => (int) $v)->all())],
            'money',
        );
    }

    private function plansChart(ReportFilters $f): array
    {
        $rows = $this->baseQuery($f)->toBase()
            ->leftJoin('memberships', 'memberships.id', '=', 'payments.membership_id')
            ->leftJoin('plans', 'plans.id', '=', 'memberships.plan_id')
            ->selectRaw('plans.name AS plan_name, SUM(payments.amount) AS total')
            ->groupBy('plans.name')
            ->orderByDesc('total')
            ->get();

        return ChartData::make(
            'bar',
            $rows->map(fn ($r) => $r->plan_name ?? 'Sin plan')->all(),
            [ChartData::dataset('Ingresos', $rows->pluck('total')->map(fn ($v) => (int) $v)->all())],
            'money',
            ['horizontal' => true],
        );
    }

    /** @return list<object{name: string, total: int, count: int}> */
    private function byStaff(ReportFilters $f): array
    {
        return $this->baseQuery($f)->toBase()
            ->leftJoin('users', 'users.id', '=', 'payments.received_by')
            ->selectRaw('users.name AS user_name, SUM(payments.amount) AS total, COUNT(*) AS n')
            ->groupBy('users.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => (object) [
                'name'  => $r->user_name ?? 'Sin dato',
                'total' => (int) $r->total,
                'count' => (int) $r->n,
            ])
            ->all();
    }

    /** @return array{count: int, total: int} */
    private function voided(ReportFilters $f): array
    {
        $row = $this->baseQuery($f, true)->toBase()
            ->whereNotNull('payments.voided_at')
            ->selectRaw('COUNT(*) AS n, COALESCE(SUM(payments.amount), 0) AS total')
            ->first();

        return ['count' => (int) $row->n, 'total' => (int) $row->total];
    }

    /** Detalle de cobros (incluye anulados, marcados). */
    private function listQuery(ReportFilters $f): Builder
    {
        return $this->baseQuery($f, true)
            ->join('members', 'members.id', '=', 'payments.member_id')
            ->leftJoin('memberships', 'memberships.id', '=', 'payments.membership_id')
            ->leftJoin('plans', 'plans.id', '=', 'memberships.plan_id')
            ->leftJoin('users', 'users.id', '=', 'payments.received_by')
            ->select([
                'payments.id',
                'payments.member_id',
                'payments.amount',
                'payments.method',
                'payments.paid_at',
                'payments.voided_at',
                'payments.void_reason',
                'payments.reference',
                'payments.notes',
                'members.first_name',
                'members.last_name',
                'members.document_number',
                'plans.name AS plan_name',
                'users.name AS received_by_name',
            ]);
    }

    private function paginate(ReportFilters $f): LengthAwarePaginator
    {
        return $this->listQuery($f)
            ->orderByDesc('payments.paid_at')
            ->orderByDesc('payments.id')
            ->paginate((int) config('reports.per_page', 15))
            ->withQueryString()
            ->through(fn ($p) => (object) [
                'id'          => $p->id,
                'member_id'   => $p->member_id,
                'paid_at'     => Carbon::parse($p->paid_at),
                'member'      => "{$p->last_name}, {$p->first_name}",
                'document'    => $p->document_number,
                'plan'        => $p->plan_name ?? 'Sin plan',
                'amount'      => (int) $p->amount,
                'method'      => config("reports.methods.{$p->method}", $p->method),
                'received_by' => $p->received_by_name ?? '—',
                'voided'      => $p->voided_at !== null,
                'void_reason' => $p->void_reason,
            ]);
    }

    // ---------------------------------------------------------------------
    // Exportación CSV
    // ---------------------------------------------------------------------

    public function exportName(): string
    {
        return 'cobros';
    }

    public function exportHeaders(): array
    {
        return [
            'Fecha', 'Socio', 'DNI', 'Plan', 'Importe', 'Método',
            'Cobrado por', 'Estado', 'Motivo de anulación', 'Referencia', 'Notas',
        ];
    }

    public function exportRows(ReportFilters $filters): iterable
    {
        $query = $this->listQuery($filters)
            ->orderBy('payments.paid_at')
            ->orderBy('payments.id');

        foreach ($query->cursor() as $p) {
            yield [
                Carbon::parse($p->paid_at)->format('d/m/Y H:i'),
                "{$p->last_name}, {$p->first_name}",
                $p->document_number,
                $p->plan_name ?? 'Sin plan',
                (int) $p->amount,
                config("reports.methods.{$p->method}", $p->method),
                $p->received_by_name,
                $p->voided_at ? 'Anulado' : 'Válido',
                $p->void_reason,
                $p->reference,
                $p->notes,
            ];
        }
    }
}
