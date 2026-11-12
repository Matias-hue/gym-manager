<?php

namespace App\Services\Reports;

use Illuminate\Support\Carbon;

/**
 * Rango de fechas inmutable (de 00:00 del primer día a 23:59:59 del último).
 */
final class ReportPeriod
{
    private function __construct(
        public readonly Carbon $from,
        public readonly Carbon $to,
    ) {}

    public static function make(Carbon $from, Carbon $to): self
    {
        if ($to->lt($from)) {
            [$from, $to] = [$to, $from];
        }

        return new self($from->copy()->startOfDay(), $to->copy()->endOfDay());
    }

    public static function preset(string $key): self
    {
        $today = today();

        return match ($key) {
            'today'      => self::make($today, $today),
            '7d'         => self::make($today->copy()->subDays(6), $today),
            '30d'        => self::make($today->copy()->subDays(29), $today),
            'last_month' => self::make(
                $today->copy()->subMonthNoOverflow()->startOfMonth(),
                $today->copy()->subMonthNoOverflow()->endOfMonth(),
            ),
            'this_year'  => self::make($today->copy()->startOfYear(), $today),
            default      => self::make($today->copy()->startOfMonth(), $today), // this_month
        };
    }

    /** Cantidad de días del rango (inclusive). */
    public function days(): int
    {
        return (int) abs($this->from->copy()->startOfDay()->diffInDays($this->to->copy()->startOfDay())) + 1;
    }

    /** Días transcurridos hasta hoy dentro del rango, para promedios justos. */
    public function elapsedDays(): int
    {
        $end = $this->to->copy()->startOfDay();
        $today = today();

        if ($end->gt($today)) {
            $end = $today;
        }

        if ($end->lt($this->from)) {
            return 1;
        }

        return max(1, (int) $this->from->copy()->startOfDay()->diffInDays($end) + 1);
    }

    /** Período inmediatamente anterior, con la misma cantidad de días. */
    public function previous(): self
    {
        $days = $this->days();
        $to = $this->from->copy()->subDay();
        $from = $to->copy()->subDays($days - 1);

        return self::make($from, $to);
    }

    /** day | month, según el largo del rango. */
    public function granularity(): string
    {
        return $this->days() > (int) config('reports.month_group_after_days', 62) ? 'month' : 'day';
    }

    /**
     * Todas las etiquetas del eje X, con ceros incluidos.
     *
     * @return array<string, string> clave de agrupación => etiqueta
     */
    public function buckets(): array
    {
        $buckets = [];

        if ($this->granularity() === 'month') {
            $cursor = $this->from->copy()->startOfMonth();
            while ($cursor->lte($this->to)) {
                $buckets[$cursor->format('Y-m')] = $cursor->translatedFormat('M Y');
                $cursor->addMonth();
            }

            return $buckets;
        }

        $cursor = $this->from->copy()->startOfDay();
        while ($cursor->lte($this->to)) {
            $buckets[$cursor->format('Y-m-d')] = $cursor->format('d/m');
            $cursor->addDay();
        }

        return $buckets;
    }

    /** Clave de agrupación para una fecha (misma convención que buckets()). */
    public function bucketKey(Carbon $date): string
    {
        return $this->granularity() === 'month' ? $date->format('Y-m') : $date->format('Y-m-d');
    }

    /** @return array{0: Carbon, 1: Carbon} para whereBetween sobre columnas datetime. */
    public function range(): array
    {
        return [$this->from, $this->to];
    }

    /** @return array{0: string, 1: string} para whereBetween sobre columnas date. */
    public function dateRange(): array
    {
        return [$this->from->toDateString(), $this->to->toDateString()];
    }

    public function equals(self $other): bool
    {
        return $this->from->equalTo($other->from) && $this->to->equalTo($other->to);
    }

    public function label(): string
    {
        if ($this->from->isSameDay($this->to)) {
            return $this->from->format('d/m/Y');
        }

        return $this->from->format('d/m/Y').' – '.$this->to->format('d/m/Y');
    }

    /** @return array{from: string, to: string} */
    public function toQuery(): array
    {
        return [
            'from' => $this->from->toDateString(),
            'to'   => $this->to->toDateString(),
        ];
    }
}
