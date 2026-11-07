{{-- PULSO DEL GIMNASIO: visitas de hoy y socios al día. Recibe $attendance y $summary. --}}
<section
    id="attendance"
    class="w-full rounded-xl border border-stone-200 bg-white p-5 sm:p-6"
    aria-labelledby="attendance-title"
>
    {{-- Encabezado --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-stone-500">
                Hoy
            </p>

            <h2
                id="attendance-title"
                class="mt-1 font-heading text-xl font-semibold tracking-tight"
            >
                Pulso del gimnasio
            </h2>
        </div>

        <span class="rounded-full bg-lime-100 px-2.5 py-1 text-xs font-medium text-lime-900">
            Actualizado {{ now()->format('H:i') }}
        </span>
    </div>

    {{-- Visitas de hoy (solo planes con límite) --}}
    <div class="mt-7 border-t border-stone-100 pt-6">
        <div class="flex items-center justify-between gap-3 text-sm">
            <span class="font-medium">
                Visitas de hoy
            </span>

            <span class="text-stone-500">
                {{ $attendance['count'] }} {{ $attendance['count'] === 1 ? 'ingreso' : 'ingresos' }}
            </span>
        </div>

        @if ($attendance['items']->isEmpty())
            <p class="mt-3 text-sm text-stone-500">
                Todavía no hubo ingresos hoy.
            </p>
        @else
            <ul class="mt-3 divide-y divide-stone-100">
                @foreach ($attendance['items'] as $item)
                    <li class="flex items-center justify-between gap-4 py-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium">
                                {{ $item['member']?->first_name }} {{ $item['member']?->last_name }}
                            </p>
                            <p class="text-xs text-stone-500">
                                {{ $item['plan']?->name ?? 'Sin plan activo' }}
                                @if ($item['limit'])
                                    · {{ $item['week_visits'] }} de {{ $item['limit'] }} visitas esta semana
                                @else
                                    · {{ $item['week_visits'] }} {{ $item['week_visits'] === 1 ? 'visita' : 'visitas' }} esta semana
                                @endif
                            </p>
                        </div>

                        <div class="flex shrink-0 flex-col items-end gap-1">
                            <span class="text-xs font-medium text-stone-500">
                                {{ $item['time']->format('H:i') }}
                            </span>

                            @if ($item['exceeded'])
                                <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-900">
                                    Superó las {{ $item['limit'] }}
                                </span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            @if ($attendance['hidden'] > 0)
                <p class="mt-1 text-xs text-stone-500">
                    Y {{ $attendance['hidden'] }} {{ $attendance['hidden'] === 1 ? 'ingreso más' : 'ingresos más' }} hoy.
                </p>
            @endif
        @endif

        <p class="mt-2 text-xs text-stone-500">
            Se avisa si un socio supera sus visitas por semana, pero no se le bloquea el ingreso. Musculación libre no se registra.
        </p>
    </div>

    {{-- Socios al día --}}
    @php
        $upToDatePercent = $summary['active'] > 0
            ? (int) round($summary['current'] / $summary['active'] * 100)
            : 0;
    @endphp

    <div class="mt-6 border-t border-stone-100 pt-6">
        <div class="flex items-center justify-between gap-3 text-sm">
            <span class="font-medium">
                Socios al día
            </span>

            <span class="text-stone-500">
                {{ $summary['current'] }} / {{ $summary['active'] }}
            </span>
        </div>

        <div
            class="mt-3 h-2 overflow-hidden rounded-full bg-stone-100"
            role="progressbar"
            aria-label="Socios activos que están al día"
            aria-valuenow="{{ $summary['current'] }}"
            aria-valuemin="0"
            aria-valuemax="{{ $summary['active'] }}"
        >
            <div class="h-full rounded-full bg-stone-800" style="width: {{ $upToDatePercent }}%"></div>
        </div>

        <p class="mt-2 text-xs text-stone-500">
            @php $others = $summary['active'] - $summary['current']; @endphp

            @if ($others > 0)
                {{ $others === 1 ? 'El otro socio activo está' : 'Los otros '.$others.' socios activos están' }} en gracia, vencidos o sin pagos
            @else
                Todos los socios activos están al día
            @endif
        </p>
    </div>
</section>