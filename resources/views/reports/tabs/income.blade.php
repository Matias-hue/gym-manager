@use('App\Support\Money')

@php
    $charts = $data['charts'];
    $payments = $data['payments'];
@endphp

<x-reports.kpi-grid :kpis="$data['kpis']" />

<div class="grid gap-6 lg:grid-cols-3">
    <x-reports.chart-card
        class="lg:col-span-2"
        title="Evolución de ingresos"
        :subtitle="$data['granularity_label']"
        :chart="$charts['trend']"
    />

    <x-reports.chart-card title="Por método de pago" :chart="$charts['methods']" />
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <x-reports.chart-card
        class="lg:col-span-2"
        title="Por plan"
        subtitle="Cobros sin plan asociado figuran como «Sin plan»"
        :chart="$charts['plans']"
        small
    />

    <x-reports.card title="Por persona que cobró">
        @forelse ($data['staff'] as $row)
            <div class="flex items-center justify-between border-b border-gray-100 py-2.5 last:border-0">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $row->name }}</p>
                    <p class="text-xs text-gray-500">{{ $row->count }} {{ $row->count === 1 ? 'cobro' : 'cobros' }}</p>
                </div>
                <p class="text-sm font-semibold tabular-nums text-gray-900">{{ Money::format($row->total) }}</p>
            </div>
        @empty
            <x-reports.empty class="h-28" />
        @endforelse
    </x-reports.card>
</div>

@if ($data['voided']['count'] > 0)
    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" role="note">
        {{ $data['voided']['count'] }}
        {{ $data['voided']['count'] === 1 ? 'cobro anulado' : 'cobros anulados' }}
        por {{ Money::format($data['voided']['total']) }} en este período.
        No se suman a los ingresos; aparecen tachados en el detalle.
    </div>
@endif

<x-reports.card
    title="Detalle de cobros"
    :subtitle="number_format($payments->total(), 0, ',', '.').' registros'"
    padding="none"
>
    <x-reports.table :headers="[
        'Fecha' => 'left',
        'Socio' => 'left',
        'Plan' => 'left',
        'Método' => 'left',
        'Cobró' => 'left',
        'Importe' => 'right',
    ]">
        @forelse ($payments as $payment)
            <tr @class(['hover:bg-gray-50', 'bg-gray-50/60 text-gray-400' => $payment->voided])>
                <td class="whitespace-nowrap px-4 py-3 tabular-nums">{{ $payment->paid_at->format('d/m/Y H:i') }}</td>
                <td class="px-4 py-3">
                    <a
                        href="{{ route('members.show', $payment->member_id) }}"
                        @class(['font-medium hover:underline', 'text-gray-900' => ! $payment->voided])
                    >{{ $payment->member }}</a>
                    @if ($payment->document)
                        <span class="block text-xs text-gray-400">DNI {{ $payment->document }}</span>
                    @endif
                </td>
                <td class="whitespace-nowrap px-4 py-3">{{ $payment->plan }}</td>
                <td class="whitespace-nowrap px-4 py-3">{{ $payment->method }}</td>
                <td class="whitespace-nowrap px-4 py-3">{{ $payment->received_by }}</td>
                <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums">
                    <span @class(['line-through' => $payment->voided, 'font-semibold text-gray-900' => ! $payment->voided])>
                        {{ Money::format($payment->amount) }}
                    </span>
                    @if ($payment->voided)
                        <span
                            class="ml-1 rounded-full bg-rose-50 px-2 py-0.5 text-xs font-medium text-rose-700 ring-1 ring-inset ring-rose-600/20"
                            @if ($payment->void_reason) title="{{ $payment->void_reason }}" @endif
                        >Anulado</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-4 py-10 text-center text-gray-400">No hay cobros con estos filtros.</td>
            </tr>
        @endforelse
    </x-reports.table>

    @if ($payments->hasPages())
        <div class="no-print border-t border-gray-100 px-5 py-3">
            {{ $payments->links() }}
        </div>
    @endif
</x-reports.card>
