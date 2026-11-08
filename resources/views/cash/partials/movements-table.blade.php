<div class="rounded-lg border border-gray-200 bg-white">
    <h2 class="border-b border-gray-100 px-6 py-4 text-lg font-medium text-gray-900">Movimientos</h2>

    @if ($movements->isEmpty())
        <p class="px-6 py-8 text-center text-sm text-gray-500">Todavía no hay movimientos en esta caja.</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Hora</th>
                        <th class="px-6 py-3">Concepto</th>
                        <th class="px-6 py-3">Medio</th>
                        <th class="px-6 py-3 text-right">Monto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($movements as $movement)
                        @php
                            $voided = $movement->isVoided();
                            $method = $movement->payment?->method ?? 'cash';
                        @endphp
                        <tr class="{{ $voided ? 'text-gray-400 line-through' : 'text-gray-800' }}">
                            <td class="whitespace-nowrap px-6 py-3">{{ $movement->created_at->format('H:i') }}</td>
                            <td class="px-6 py-3">
                                {{ $movement->concept }}
                                @if ($voided)
                                    <span class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-500 no-underline">Anulado</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-3">
                                {{ \App\Models\CashSession::METHOD_LABELS[$method] ?? $method }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-3 text-right font-medium {{ $voided ? '' : ($movement->type === 'income' ? 'text-green-700' : 'text-red-700') }}">
                                {{ $movement->type === 'income' ? '+' : '-' }} $ {{ number_format($movement->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
