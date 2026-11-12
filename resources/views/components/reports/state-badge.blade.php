@props(['state'])

@php
    $classes = match ($state) {
        'current' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'grace'   => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'expired' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        default   => 'bg-gray-100 text-gray-600 ring-gray-500/20',
    };
@endphp

<span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $classes }}">
    {{ config("reports.states.{$state}", $state) }}
</span>
