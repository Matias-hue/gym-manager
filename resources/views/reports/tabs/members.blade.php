@php
    $charts = $data['charts'];
    $listHeaders = [
        'Socio' => 'left',
        'Plan' => 'left',
        'Estado' => 'left',
        'Vence' => 'left',
        'Días' => 'right',
        '' => 'right',
    ];
@endphp

<x-reports.kpi-grid :kpis="$data['kpis']" cols="3" />

<div class="grid gap-6 lg:grid-cols-3">
    <x-reports.chart-card title="Estado de la cuota" subtitle="Socios activos, hoy" :chart="$charts['states']" />

    <x-reports.chart-card
        class="lg:col-span-2"
        title="Altas en el período"
        :subtitle="'Socios nuevos por '.($filters->period->granularity() === 'month' ? 'mes' : 'día')"
        :chart="$charts['new_members']"
    />
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <x-reports.chart-card
        class="lg:col-span-2"
        title="Socios por plan"
        subtitle="Solo cuotas al día"
        :chart="$charts['plans']"
        small
    />

    <x-reports.card title="Cupos de turnos" subtitle="Planes con cupo limitado">
        @forelse ($data['occupancy'] as $row)
            <x-reports.progress
                class="border-b border-gray-100 py-3 first:pt-0 last:border-0 last:pb-0"
                :label="$row['plan'].' · '.$row['slot']"
                :used="$row['used']"
                :capacity="$row['capacity']"
                :pct="$row['pct']"
            />
        @empty
            <x-reports.empty class="h-28">Ningún plan tiene cupo limitado.</x-reports.empty>
        @endforelse
    </x-reports.card>
</div>

<x-reports.card
    title="Vencen pronto"
    :subtitle="$data['expiring']['total'].' con cuota al día que vencen en los próximos '.$data['expiring']['days'].' días'"
    padding="none"
>
    <x-reports.table :headers="$listHeaders">
        @forelse ($data['expiring']['rows'] as $row)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-900">
                    <a href="{{ route('members.show', $row->member_id) }}" class="hover:underline">{{ $row->name }}</a>
                </td>
                <td class="whitespace-nowrap px-4 py-3">{{ $row->plan }}</td>
                <td class="px-4 py-3"><x-reports.state-badge :state="$row->state" /></td>
                <td class="whitespace-nowrap px-4 py-3 tabular-nums">{{ $row->ends_on->format('d/m/Y') }}</td>
                <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums">
                    {{ $row->days_left === 0 ? 'Hoy' : 'En '.$row->days.' '.($row->days === 1 ? 'día' : 'días') }}
                </td>
                <td class="px-4 py-3 text-right"><x-reports.whatsapp :url="$row->wa_url" /></td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-4 py-10 text-center text-gray-400">Nadie vence en los próximos días.</td>
            </tr>
        @endforelse
    </x-reports.table>

    @if ($data['expiring']['total'] > $data['list_limit'])
        <p class="border-t border-gray-100 px-5 py-3 text-xs text-gray-500">
            Se muestran {{ $data['list_limit'] }} de {{ $data['expiring']['total'] }}. La lista completa está en la exportación CSV.
        </p>
    @endif
</x-reports.card>

<x-reports.card
    title="Con la cuota vencida"
    :subtitle="$data['overdue']['total'].' socios en gracia o vencidos, del vencimiento más reciente al más antiguo'"
    padding="none"
>
    <x-reports.table :headers="$listHeaders">
        @forelse ($data['overdue']['rows'] as $row)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-900">
                    <a href="{{ route('members.show', $row->member_id) }}" class="hover:underline">{{ $row->name }}</a>
                </td>
                <td class="whitespace-nowrap px-4 py-3">{{ $row->plan }}</td>
                <td class="px-4 py-3"><x-reports.state-badge :state="$row->state" /></td>
                <td class="whitespace-nowrap px-4 py-3 tabular-nums">{{ $row->ends_on->format('d/m/Y') }}</td>
                <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums">
                    Hace {{ $row->days }} {{ $row->days === 1 ? 'día' : 'días' }}
                </td>
                <td class="px-4 py-3 text-right"><x-reports.whatsapp :url="$row->wa_url" /></td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-4 py-10 text-center text-gray-400">No hay socios con la cuota vencida.</td>
            </tr>
        @endforelse
    </x-reports.table>

    @if ($data['overdue']['total'] > $data['list_limit'])
        <p class="border-t border-gray-100 px-5 py-3 text-xs text-gray-500">
            Se muestran {{ $data['list_limit'] }} de {{ $data['overdue']['total'] }}. La lista completa está en la exportación CSV.
        </p>
    @endif
</x-reports.card>
