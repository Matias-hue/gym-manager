@props([
    'kpis' => [],
    'cols' => 4, // columnas en pantallas grandes: 3 | 4 | 5
])

@php
    $columns = match ((int) $cols) {
        3 => 'lg:grid-cols-3',
        5 => 'lg:grid-cols-5',
        default => 'lg:grid-cols-4',
    };
@endphp

<div {{ $attributes->class(['grid grid-cols-1 gap-4 sm:grid-cols-2', $columns]) }}>
    @foreach ($kpis as $kpi)
        <x-reports.kpi
            :label="$kpi['label']"
            :value="$kpi['value']"
            :format="$kpi['format']"
            :delta="$kpi['delta']"
            :inverse="$kpi['inverse']"
            :hint="$kpi['hint']"
        />
    @endforeach
</div>
