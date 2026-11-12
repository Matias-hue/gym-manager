@php
    $charts = $data['charts'];
    $inactive = $data['inactive'];
@endphp

<x-reports.kpi-grid :kpis="$data['kpis']" cols="5" />

<x-reports.chart-card
    title="Visitas en el tiempo"
    :subtitle="$filters->period->granularity() === 'month' ? 'Agrupado por mes' : 'Agrupado por día'"
    :chart="$charts['trend']"
    small
/>

<div class="grid gap-6 lg:grid-cols-3">
    <x-reports.chart-card title="Por día de la semana" :chart="$charts['weekday']" small />
    <x-reports.chart-card title="Por hora del día" subtitle="Horas con visitas" :chart="$charts['hours']" small />
    <x-reports.chart-card title="Cómo ingresan" subtitle="Método de registro" :chart="$charts['methods']" small />
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <x-reports.card title="Los más asiduos" subtitle="Socios con más visitas en el período" padding="none">
        <x-reports.table :headers="['#' => 'left', 'Socio' => 'left', 'Visitas' => 'right', 'Última visita' => 'right']">
            @forelse ($data['top'] as $row)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 tabular-nums text-gray-400">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3 font-medium text-gray-900">
                        <a href="{{ route('members.show', $row->member_id) }}" class="hover:underline">{{ $row->name }}</a>
                    </td>
                    <td class="px-4 py-3 text-right font-semibold tabular-nums">{{ $row->visits }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums text-gray-500">{{ $row->last_visit->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-10 text-center text-gray-400">No hay visitas en este período.</td>
                </tr>
            @endforelse
        </x-reports.table>
    </x-reports.card>

    <x-reports.card
        title="Cuota al día pero sin venir"
        :subtitle="$inactive['total'].' socios sin visitas hace más de '.$inactive['days'].' días (hoy, sin importar el período)'"
        padding="none"
    >
        <x-reports.table :headers="['Socio' => 'left', 'Última visita' => 'left', 'Sin venir' => 'right', '' => 'right']">
            @forelse ($inactive['rows'] as $row)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">
                        <a href="{{ route('members.show', $row->member_id) }}" class="hover:underline">{{ $row->name }}</a>
                        <span class="block text-xs font-normal text-gray-400">{{ $row->plan }}</span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 tabular-nums">
                        {{ $row->last_visit ? $row->last_visit->format('d/m/Y') : 'Nunca' }}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums">
                        {{ $row->days_since === null ? '—' : $row->days_since.' '.($row->days_since === 1 ? 'día' : 'días') }}
                    </td>
                    <td class="px-4 py-3 text-right"><x-reports.whatsapp :url="$row->wa_url" /></td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-10 text-center text-gray-400">Todos los socios al día vinieron hace poco.</td>
                </tr>
            @endforelse
        </x-reports.table>

        @if ($inactive['total'] > count($inactive['rows']))
            <p class="border-t border-gray-100 px-5 py-3 text-xs text-gray-500">
                Se muestran {{ count($inactive['rows']) }} de {{ $inactive['total'] }}.
            </p>
        @endif
    </x-reports.card>
</div>
