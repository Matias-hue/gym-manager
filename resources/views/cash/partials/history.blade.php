<div class="rounded-lg border border-gray-200 bg-white">
    <h2 class="border-b border-gray-100 px-6 py-4 text-lg font-medium text-gray-900">Cajas anteriores</h2>

    @if ($history->isEmpty())
        <p class="px-6 py-8 text-center text-sm text-gray-500">Todavía no se cerró ninguna caja.</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Fecha</th>
                        <th class="px-6 py-3">Abrió</th>
                        <th class="px-6 py-3 text-right">Esperado</th>
                        <th class="px-6 py-3 text-right">Contado</th>
                        <th class="px-6 py-3 text-right">Diferencia</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($history as $past)
                        @php
                            $difference = $past->difference();
                            $tone = match (true) {
                                $difference === null || $difference === 0 => 'text-green-700',
                                $difference < 0 => 'text-red-700',
                                default => 'text-amber-700',
                            };
                        @endphp
                        <tr class="text-gray-800">
                            <td class="whitespace-nowrap px-6 py-3">{{ $past->opened_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-3">{{ $past->openedBy->name }}</td>
                            <td class="whitespace-nowrap px-6 py-3 text-right">$ {{ number_format($past->expected_amount, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap px-6 py-3 text-right">$ {{ number_format($past->counted_amount, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap px-6 py-3 text-right font-medium {{ $tone }}">
                                {{ $difference > 0 ? '+' : '' }}$ {{ number_format($difference, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('cash.show', $past) }}" class="text-gray-600 underline hover:text-gray-900">Ver</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($history->hasPages())
            <div class="border-t border-gray-100 px-6 py-3">
                {{ $history->links() }}
            </div>
        @endif
    @endif
</div>
