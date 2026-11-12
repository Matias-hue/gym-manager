<?php

namespace App\Support;

/**
 * Arma el arreglo que consume el componente <x-reports.kpi>.
 *
 * $format: money | number | decimal | percent | text
 * $inverse: true cuando "bajar" es bueno (por ejemplo, pagos anulados).
 */
final class Kpi
{
    public static function make(
        string $label,
        int|float|string|null $value,
        string $format = 'number',
        ?float $delta = null,
        bool $inverse = false,
        ?string $hint = null,
    ): array {
        return compact('label', 'value', 'format', 'delta', 'inverse', 'hint');
    }
}
