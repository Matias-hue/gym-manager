<?php

namespace App\Services\Reports;

/**
 * Estructura neutra que lee resources/js/reports/charts.js.
 *
 * $type:    line | bar | doughnut
 * $format:  number | money | percent  (formato de valores y tooltips)
 * $options: horizontal (bool), tones (array de success|warning|danger|neutral|info)
 */
final class ChartData
{
    public static function make(
        string $type,
        array $labels,
        array $datasets,
        string $format = 'number',
        array $options = [],
    ): array {
        return [
            'type'     => $type,
            'labels'   => array_values($labels),
            'datasets' => $datasets,
            'format'   => $format,
            'options'  => (object) $options,
        ];
    }

    public static function dataset(string $label, array $data): array
    {
        return [
            'label' => $label,
            'data'  => array_map(fn ($value) => $value + 0, array_values($data)),
        ];
    }
}
