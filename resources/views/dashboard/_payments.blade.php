{{-- COBROS RECIENTES. Recibe $payments (los últimos pagos no anulados). --}}
<section
    id="payments"
    class="w-full rounded-xl border border-stone-200 bg-white p-5 sm:p-6"
    aria-labelledby="payments-title"
>

    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-stone-500">
            Caja
        </p>

        <h2
            id="payments-title"
            class="mt-1 font-heading text-xl font-semibold tracking-tight"
        >
            Cobros recientes
        </h2>
    </div>

    <div class="mt-5 overflow-x-auto">

        <table class="w-full min-w-[640px] text-left text-sm">

            <caption class="sr-only">
                Últimos cobros registrados
            </caption>

            <thead class="border-b border-stone-200 text-xs font-medium text-stone-500">
                <tr>
                    <th scope="col" class="pb-3 pr-4">Socio</th>
                    <th scope="col" class="pb-3 pr-4">Plan</th>
                    <th scope="col" class="pb-3 pr-4">Fecha</th>
                    <th scope="col" class="pb-3 pr-4">Monto</th>
                    <th scope="col" class="pb-3">Forma de pago</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-stone-100">
                @forelse ($payments as $payment)
                    @php
                        $member = $payment->member;

                        $document = $member && ctype_digit((string) $member->document_number)
                            ? number_format((int) $member->document_number, 0, ',', '.')
                            : $member?->document_number;

                        $method = match ($payment->method) {
                            'cash' => 'Efectivo',
                            'transfer' => 'Transferencia',
                            'card' => 'Tarjeta',
                            'online' => 'Online',
                            default => ucfirst((string) $payment->method),
                        };

                        $date = $payment->paid_at->isToday()
                            ? 'Hoy '.$payment->paid_at->format('H:i')
                            : $payment->paid_at->locale('es')->isoFormat('D MMM');
                    @endphp

                    <tr>
                        <td class="py-3.5 pr-4 font-medium">
                            {{ $member?->first_name }} {{ $member?->last_name }}
                            <span class="block text-xs font-normal text-stone-400">
                                DNI {{ $document }}
                            </span>
                        </td>
                        <td class="py-3.5 pr-4 text-stone-600">
                            {{ $payment->membership?->plan?->name ?? '—' }}
                        </td>
                        <td class="py-3.5 pr-4 text-stone-600">
                            {{ $date }}
                        </td>
                        <td class="py-3.5 pr-4">
                            $ {{ number_format($payment->amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 text-stone-600">
                            {{ $method }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-sm text-stone-500">
                            Todavía no hay cobros registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>

    </div>

</section>
