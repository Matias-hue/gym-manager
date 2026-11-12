@props([
    'title',
    'subtitle' => null,
    'chart',          // arreglo armado con App\Services\Reports\ChartData
    'small' => false, // altura reducida
])

@php
    $isEmpty = collect($chart['datasets'])->every(fn ($dataset) => collect($dataset['data'])->sum() == 0);
@endphp

<x-reports.card :title="$title" :subtitle="$subtitle" {{ $attributes }}>
    @if ($isEmpty)
        <x-reports.empty />
    @else
        <div @class(['chart-box', 'chart-box--sm' => $small])>
            <canvas
                role="img"
                aria-label="{{ $title }}"
                data-chart="{{ json_encode($chart, JSON_UNESCAPED_UNICODE) }}"
            ></canvas>
        </div>
    @endif
</x-reports.card>
