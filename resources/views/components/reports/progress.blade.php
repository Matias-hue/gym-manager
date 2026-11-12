@props([
    'label',
    'used' => 0,
    'capacity' => 0,
    'pct' => 0,
])

@php
    $bar = match (true) {
        $pct >= 100 => 'bg-rose-500',
        $pct >= 80  => 'bg-amber-500',
        default     => 'bg-emerald-500',
    };
@endphp

<div {{ $attributes }}>
    <div class="flex items-baseline justify-between text-sm">
        <span class="font-medium text-gray-700">{{ $label }}</span>
        <span class="tabular-nums text-gray-500">{{ $used }} / {{ $capacity }} <span class="text-xs">({{ $pct }} %)</span></span>
    </div>
    <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-gray-100" role="progressbar" aria-valuenow="{{ min($pct, 100) }}" aria-valuemin="0" aria-valuemax="100">
        <div class="h-full rounded-full {{ $bar }}" style="width: {{ min($pct, 100) }}%"></div>
    </div>
</div>
