<?php

namespace App\Services\Reports\Concerns;

use App\Services\Reports\ReportPeriod;
use Illuminate\Support\Facades\DB;

/**
 * Expresiones SQL que cambian según el motor, para que los reportes funcionen
 * igual con SQLite (desarrollo), MySQL/MariaDB (piloto) y PostgreSQL.
 */
trait UsesSqlExpressions
{
    private function sqlDriver(): string
    {
        return DB::connection()->getDriverName();
    }

    private function sqlDate(string $column): string
    {
        return "DATE({$column})";
    }

    /** Año y mes, "2026-11". */
    private function sqlMonth(string $column): string
    {
        return match ($this->sqlDriver()) {
            'sqlite' => "strftime('%Y-%m', {$column})",
            'pgsql'  => "TO_CHAR({$column}, 'YYYY-MM')",
            default  => "DATE_FORMAT({$column}, '%Y-%m')",
        };
    }

    /** Hora del día, 0 a 23. */
    private function sqlHour(string $column): string
    {
        return match ($this->sqlDriver()) {
            'sqlite' => "CAST(strftime('%H', {$column}) AS INTEGER)",
            'pgsql'  => "EXTRACT(HOUR FROM {$column})",
            default  => "HOUR({$column})",
        };
    }

    /** Día ISO de la semana, 1 (lunes) a 7 (domingo). */
    private function sqlIsoWeekday(string $column): string
    {
        return match ($this->sqlDriver()) {
            'sqlite' => "CASE strftime('%w', {$column}) WHEN '0' THEN 7 ELSE CAST(strftime('%w', {$column}) AS INTEGER) END",
            'pgsql'  => "EXTRACT(ISODOW FROM {$column})",
            default  => "(WEEKDAY({$column}) + 1)",
        };
    }

    /** Clave de agrupación según la granularidad del período (día o mes). */
    private function sqlBucket(ReportPeriod $period, string $column): string
    {
        return $period->granularity() === 'month'
            ? $this->sqlMonth($column)
            : $this->sqlDate($column);
    }
}
