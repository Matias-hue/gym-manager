{{-- Etiqueta del estado de la cuota. Recibe $state: current | grace | expired | cancelled | none --}}
@php
    $states = [
        'current'   => ['Al día', 'bg-lime-100 text-lime-900'],
        'grace'     => ['En gracia', 'bg-amber-100 text-amber-900'],
        'expired'   => ['Vencido', 'bg-red-100 text-red-900'],
        'cancelled' => ['Cancelada', 'bg-stone-100 text-stone-700'],
        'none'      => ['Sin pagos', 'bg-stone-100 text-stone-700'],
    ];

    [$stateLabel, $stateClasses] = $states[$state] ?? $states['none'];
@endphp

<span class="inline-block rounded-full px-2.5 py-1 text-xs font-medium {{ $stateClasses }}">
    {{ $stateLabel }}
</span>
