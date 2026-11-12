@props([
    'label',
    'value' => null,
    'format' => 'number', // money | number | decimal | percent | text
    'delta' => null,      // variación % contra el período anterior
    'inverse' => false,   // true cuando "bajar" es bueno
    'hint' => null,
])

@use('App\Support\Money')

@php
    $display = match ($format) {
        'money'   => Money::format($value),
        'percent' => $value === null ? '—' : number_format((float) $value, 1, ',', '.').' %',
        'decimal' => number_format((float) $value, 1, ',', '.'),
        'text'    => (string) $value,
        default   => number_format((float) $value, 0, ',', '.'),
    };

    $good = $delta === null ? null : ($inverse ? $delta <= 0 : $delta >= 0);
@endphp

<div {{ $attributes->class(['report-card rounded-xl border border-gray-200 bg-white p-5 shadow-sm']) }}>
    <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
    <p class="mt-2 text-2xl font-semibold tabular-nums text-gray-900">{{ $display }}</p>

    @if ($delta !== null)
        <p class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs">
            <span @class([
                'inline-flex items-center gap-1 rounded-full px-2 py-0.5 font-medium',
                'bg-emerald-50 text-emerald-700' => $good,
                'bg-rose-50 text-rose-700' => ! $good,
            ])>
                <span aria-hidden="true">{{ $delta > 0 ? '▲' : ($delta < 0 ? '▼' : '■') }}</span>
                {{ number_format(abs($delta), 1, ',', '.') }} %
            </span>
            <span class="text-gray-400">vs. período anterior</span>
        </p>
    @endif

    @if ($hint)
        <p class="mt-2 text-xs text-gray-500">{{ $hint }}</p>
    @endif
</div>
