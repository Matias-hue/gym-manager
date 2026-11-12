@use('App\Support\Money')

@php
    $sessions = $data['sessions'];
    $concepts = $data['concepts'];
@endphp

<x-reports.kpi-grid :kpis="$data['kpis']" cols="5" />

<x-reports.chart-card
    title="Ingresos y egresos por sesión de caja"
    :subtitle="$filters->period->granularity() === 'month' ? 'Agrupado por mes de apertura' : 'Agrupado por día de apertura'"
    :chart="$data['chart']"
    small
/>

<div class="grid gap-6 lg:grid-cols-2">
    <x-reports.card title="Otros ingresos" subtitle="Movimientos manuales, por concepto">
        <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
            <p class="text-sm font-medium text-gray-800">Cobro de cuotas</p>
            <p class="text-sm font-semibold tabular-nums text-gray-900">{{ Money::format($data['fees']) }}</p>
        </div>
        @forelse ($concepts['income'] as $row)
            <div class="flex items-center justify-between border-b border-gray-100 py-2.5 last:border-0">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $row->concept }}</p>
                    <p class="text-xs text-gray-500">{{ $row->count }} {{ $row->count === 1 ? 'movimiento' : 'movimientos' }}</p>
                </div>
                <p class="text-sm font-semibold tabular-nums text-emerald-700">{{ Money::format($row->total) }}</p>
            </div>
        @empty
            <p class="pt-3 text-sm text-gray-400">Sin otros ingresos cargados a mano.</p>
        @endforelse
    </x-reports.card>

    <x-reports.card title="Egresos" subtitle="Por concepto">
        @forelse ($concepts['expense'] as $row)
            <div class="flex items-center justify-between border-b border-gray-100 py-2.5 first:pt-0 last:border-0">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $row->concept }}</p>
                    <p class="text-xs text-gray-500">{{ $row->count }} {{ $row->count === 1 ? 'movimiento' : 'movimientos' }}</p>
                </div>
                <p class="text-sm font-semibold tabular-nums text-rose-700">{{ Money::format($row->total) }}</p>
            </div>
        @empty
            <x-reports.empty class="h-28">Sin egresos en el período.</x-reports.empty>
        @endforelse
    </x-reports.card>
</div>

<x-reports.card
    title="Sesiones de caja"
    :subtitle="number_format($sessions->total(), 0, ',', '.').' sesiones abiertas en el período'"
    padding="none"
>
    <x-reports.table :headers="[
        'Apertura' => 'left',
        'Abrió' => 'left',
        'Fondo' => 'right',
        'Ingresos' => 'right',
        'Egresos' => 'right',
        'Esperado' => 'right',
        'Contado' => 'right',
        'Diferencia' => 'right',
        'Estado' => 'left',
    ]">
        @forelse ($sessions as $session)
            <tr class="hover:bg-gray-50">
                <td class="whitespace-nowrap px-4 py-3 tabular-nums">
                    {{ $session->opened_at->format('d/m/Y H:i') }}
                    @if ($session->closed_at)
                        <span class="block text-xs text-gray-400">cerró {{ $session->closed_at->format('H:i') }}</span>
                    @endif
                </td>
                <td class="whitespace-nowrap px-4 py-3">{{ $session->opener }}</td>
                <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums">{{ Money::format($session->opening) }}</td>
                <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums text-emerald-700">{{ Money::format($session->income) }}</td>
                <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums text-rose-700">{{ Money::format($session->expense) }}</td>
                <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums font-medium">{{ Money::format($session->expected) }}</td>
                <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums">
                    {{ $session->counted === null ? '—' : Money::format($session->counted) }}
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-right">
                    @if ($session->difference === null)
                        <span class="text-gray-400">—</span>
                    @elseif ($session->difference === 0)
                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Cuadra</span>
                    @elseif ($session->difference < 0)
                        <span class="rounded-full bg-rose-50 px-2 py-0.5 text-xs font-medium tabular-nums text-rose-700 ring-1 ring-inset ring-rose-600/20">
                            Faltan {{ Money::format(abs($session->difference)) }}
                        </span>
                    @else
                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium tabular-nums text-amber-700 ring-1 ring-inset ring-amber-600/20">
                            Sobran {{ Money::format($session->difference) }}
                        </span>
                    @endif
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    @if ($session->closed)
                        <span class="text-gray-500">Cerrada</span>
                    @else
                        <span class="font-medium text-indigo-600">Abierta</span>
                    @endif
                    @if ($session->notes)
                        <span class="block max-w-[14rem] truncate text-xs text-gray-400" title="{{ $session->notes }}">{{ $session->notes }}</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="px-4 py-10 text-center text-gray-400">No hay sesiones de caja en este período.</td>
            </tr>
        @endforelse
    </x-reports.table>

    @if ($sessions->hasPages())
        <div class="no-print border-t border-gray-100 px-5 py-3">
            {{ $sessions->links() }}
        </div>
    @endif
</x-reports.card>
