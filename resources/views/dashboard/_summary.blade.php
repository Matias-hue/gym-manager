{{-- RESUMEN: las cuatro tarjetas con los números de hoy. Recibe $summary. --}}
@php
    $paymentsText = match ($summary['payments_today']) {
        0 => 'Todavía no hay cobros hoy',
        1 => '1 cobro registrado',
        default => $summary['payments_today'].' cobros registrados',
    };
@endphp

<div class="mt-7 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

    <article class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm shadow-stone-900/[0.02]">
        <p class="text-sm text-stone-500">
            Socios activos
        </p>

        <p class="mt-3 font-heading text-3xl font-semibold tracking-tight">
            {{ $summary['active'] }}
        </p>

        <p class="mt-2 text-xs text-stone-500">
            {{ $summary['current'] }} al día · {{ $summary['grace'] }} en gracia
            @if ($summary['without_fee'] > 0)
                · {{ $summary['without_fee'] }} {{ $summary['without_fee'] === 1 ? 'vencido o sin pagos' : 'vencidos o sin pagos' }}
            @endif
        </p>
    </article>

    <article class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm shadow-stone-900/[0.02]">
        <p class="text-sm text-stone-500">
            Cobrado hoy
        </p>

        <p class="mt-3 font-heading text-3xl font-semibold tracking-tight">
            $ {{ number_format($summary['collected_today'], 0, ',', '.') }}
        </p>

        <p class="mt-2 text-xs text-stone-500">
            {{ $paymentsText }}
        </p>
    </article>

    <article class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm shadow-stone-900/[0.02]">
        <p class="text-sm text-stone-500">
            Visitas de hoy
        </p>

        <p class="mt-3 font-heading text-3xl font-semibold tracking-tight">
            {{ $summary['visits_today'] }}
        </p>

        <p class="mt-2 text-xs text-stone-500">
            Solo socios de musculación 3 días y spinning
        </p>
    </article>

    <article class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm shadow-stone-900/[0.02]">
        <p class="text-sm text-stone-500">
            Cuotas por cobrar
        </p>

        <p class="mt-3 font-heading text-3xl font-semibold tracking-tight">
            {{ $summary['receivable'] }}
        </p>

        <p class="mt-2 text-xs text-stone-500">
            {{ $summary['grace'] }} en gracia y {{ $summary['expired'] }} {{ $summary['expired'] === 1 ? 'vencida' : 'vencidas' }}
        </p>
    </article>

</div>