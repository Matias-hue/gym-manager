@props(['membership', 'compact' => false])

@php
    // compact = botón chico para tablas (inicio); normal = el de la lista de vencimientos
    $size = $compact
        ? 'min-h-11 rounded-md px-3 py-2 text-xs'
        : 'min-h-11 rounded-lg px-4 py-2.5 text-sm';
@endphp

{{-- Botón "Prórroga": solo muestra datos; el modal y el JS hacen el resto --}}
@if ($membership && $membership->status !== 'cancelled')
    <button
        type="button"
        data-extend-grace
        data-url="{{ route('memberships.extend', $membership) }}"
        data-member="{{ $membership->member->first_name }} {{ $membership->member->last_name }}"
        data-ends-on="{{ $membership->ends_on->format('d/m/Y') }}"
        data-min-date="{{ $membership->ends_on->copy()->addDay()->max(today())->toDateString() }}"
        {{ $attributes->merge(['class' => 'inline-flex items-center justify-center border border-stone-300 bg-white font-semibold text-stone-950 transition hover:bg-stone-50 '.$size]) }}
    >
        Prórroga
    </button>
@endif