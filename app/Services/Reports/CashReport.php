<?php

namespace App\Services\Reports;

use App\Models\CashMovement;
use App\Models\CashSession;
use App\Services\Reports\Contracts\ExportableReport;
use App\Support\Kpi;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Caja: sesiones abiertas dentro del período, con lo que se esperaba en el
 * cajón (fondo + ingresos − egresos) contra lo que se contó al cerrar.
 */
final class CashReport implements ExportableReport
{
    public function build(ReportFilters $f): array
    {
        $f = $f->withoutExtraFilters();

        $sessions = $this->sessions($f);

        $income = (int) $sessions->sum('income');
        $expense = (int) $sessions->sum('expense');
        $closed = $sessions->where('closed', true)->count();

        $differences = $sessions->pluck('difference')->filter(fn ($d) => $d !== null);
        $shortage = abs((int) $differences->filter(fn ($d) => $d < 0)->sum());
        $surplus = (int) $differences->filter(fn ($d) => $d > 0)->sum();
        $net = $surplus - $shortage;

        $fees = $this->movements($f)->toBase()
            ->where('cash_movements.type', 'income')
            ->whereNotNull('cash_movements.payment_id')
            ->sum('cash_movements.amount');

        return [
            'kpis' => [
                Kpi::make('Sesiones de caja', $sessions->count(), 'number', null, false, ($sessions->count() - $closed) > 0 ? ($sessions->count() - $closed).' abierta(s)' : 'Todas cerradas'),
                Kpi::make('Ingresos', $income, 'money', null, false, 'Cuotas y otros movimientos'),
                Kpi::make('Egresos', $expense, 'money', null, true),
                Kpi::make('Neto de movimientos', $income - $expense, 'money'),
                Kpi::make(
                    'Diferencia al cierre',
                    $net,
                    'money',
                    null,
                    true,
                    $shortage === 0 && $surplus === 0
                        ? 'Todas las cajas cuadraron'
                        : 'Faltan '.Money::format($shortage).' · sobran '.Money::format($surplus),
                ),
            ],
            'chart'    => $this->flowChart($f, $sessions),
            'fees'     => (int) $fees,
            'concepts' => $this->concepts($f),
            'sessions' => $this->paginate($sessions),
        ];
    }

    // ---------------------------------------------------------------------
    // Consultas
    // ---------------------------------------------------------------------

    /** Sesiones del período con sus ingresos y egresos ya sumados. */
    private function sessionsQuery(ReportFilters $f): Builder
    {
        $total = fn (string $type) => CashMovement::query()
            ->selectRaw('COALESCE(SUM(cash_movements.amount), 0)')
            ->whereColumn('cash_movements.cash_session_id', 'cash_sessions.id')
            ->where('cash_movements.type', $type);

        return CashSession::query()
            ->leftJoin('users', 'users.id', '=', 'cash_sessions.opened_by')
            ->where('cash_sessions.gym_id', $f->gymId())
            ->whereBetween('cash_sessions.opened_at', $f->period->range())
            ->select('cash_sessions.*', 'users.name AS opener_name')
            ->selectSub($total('income'), 'income_total')
            ->selectSub($total('expense'), 'expense_total');
    }

    /** @return Collection<int, object> */
    private function sessions(ReportFilters $f): Collection
    {
        return $this->sessionsQuery($f)
            ->orderByDesc('cash_sessions.opened_at')
            ->get()
            ->map(function ($s) {
                $opening = (int) $s->opening_amount;
                $income = (int) $s->income_total;
                $expense = (int) $s->expense_total;
                $expected = $opening + $income - $expense;
                $closed = $s->closed_at !== null;
                $counted = $s->counted_amount !== null ? (int) $s->counted_amount : null;

                return (object) [
                    'id'         => $s->id,
                    'opened_at'  => Carbon::parse($s->opened_at),
                    'closed_at'  => $closed ? Carbon::parse($s->closed_at) : null,
                    'opener'     => $s->opener_name ?? '—',
                    'opening'    => $opening,
                    'income'     => $income,
                    'expense'    => $expense,
                    'expected'   => $expected,
                    'counted'    => $counted,
                    'difference' => ($closed && $counted !== null) ? $counted - $expected : null,
                    'closed'     => $closed,
                    'notes'      => $s->notes,
                ];
            });
    }

    /** Movimientos de las sesiones del período. */
    private function movements(ReportFilters $f): Builder
    {
        return CashMovement::query()
            ->join('cash_sessions', 'cash_sessions.id', '=', 'cash_movements.cash_session_id')
            ->where('cash_sessions.gym_id', $f->gymId())
            ->whereBetween('cash_sessions.opened_at', $f->period->range());
    }

    /**
     * Movimientos manuales (sin pago asociado) agrupados por concepto. Los
     * cobros de cuotas ya tienen su propio reporte, por eso se separan.
     *
     * @return array{income: list<object>, expense: list<object>}
     */
    private function concepts(ReportFilters $f): array
    {
        $rows = $this->movements($f)->toBase()
            ->whereNull('cash_movements.payment_id')
            ->selectRaw('cash_movements.type, cash_movements.concept, SUM(cash_movements.amount) AS total, COUNT(*) AS n')
            ->groupBy('cash_movements.type', 'cash_movements.concept')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => (object) [
                'type'    => $r->type,
                'concept' => $r->concept,
                'total'   => (int) $r->total,
                'count'   => (int) $r->n,
            ]);

        $limit = (int) config('reports.top_limit', 10);

        return [
            'income'  => $rows->where('type', 'income')->take($limit)->values()->all(),
            'expense' => $rows->where('type', 'expense')->take($limit)->values()->all(),
        ];
    }

    private function flowChart(ReportFilters $f, Collection $sessions): array
    {
        $buckets = $f->period->buckets();
        $income = array_fill_keys(array_keys($buckets), 0);
        $expense = array_fill_keys(array_keys($buckets), 0);

        foreach ($sessions as $s) {
            $key = $f->period->bucketKey($s->opened_at);
            if (isset($income[$key])) {
                $income[$key] += $s->income;
                $expense[$key] += $s->expense;
            }
        }

        return ChartData::make(
            'bar',
            array_values($buckets),
            [
                ChartData::dataset('Ingresos', $income),
                ChartData::dataset('Egresos', $expense),
            ],
            'money',
            ['tones' => ['success', 'danger']],
        );
    }

    private function paginate(Collection $sessions): LengthAwarePaginator
    {
        $perPage = (int) config('reports.per_page', 15);
        $page = Paginator::resolveCurrentPage();

        return (new LengthAwarePaginator(
            $sessions->forPage($page, $perPage)->values(),
            $sessions->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()],
        ))->withQueryString();
    }

    // ---------------------------------------------------------------------
    // Exportación CSV
    // ---------------------------------------------------------------------

    public function exportName(): string
    {
        return 'caja';
    }

    public function exportHeaders(): array
    {
        return [
            'Apertura', 'Cierre', 'Abierta por', 'Fondo inicial', 'Ingresos',
            'Egresos', 'Esperado', 'Contado', 'Diferencia', 'Estado', 'Notas',
        ];
    }

    public function exportRows(ReportFilters $filters): iterable
    {
        foreach ($this->sessions($filters)->sortBy(fn ($s) => $s->opened_at->timestamp) as $s) {
            yield [
                $s->opened_at->format('d/m/Y H:i'),
                $s->closed_at?->format('d/m/Y H:i'),
                $s->opener,
                $s->opening,
                $s->income,
                $s->expense,
                $s->expected,
                $s->counted,
                $s->difference,
                $s->closed ? 'Cerrada' : 'Abierta',
                $s->notes,
            ];
        }
    }
}
