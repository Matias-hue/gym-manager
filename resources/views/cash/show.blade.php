<x-gym-layout>
    @php
        $isOpen = $session->isOpen();
        $expected = $isOpen ? $summary['expected'] : $session->expected_amount;
        $difference = $session->difference();

        if ($difference === null || $difference === 0) {
            $tone = 'bg-green-50 text-green-700';
        } elseif ($difference < 0) {
            $tone = 'bg-red-50 text-red-700';
        } else {
            $tone = 'bg-amber-50 text-amber-700';
        }
    @endphp

    <div class="mx-auto max-w-5xl space-y-6 p-4 sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <a href="{{ route('cash.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Volver a la caja</a>
                <h1 class="mt-1 text-2xl font-semibold text-gray-900">
                    Caja del {{ $session->opened_at->format('d/m/Y') }}
                </h1>
                <p class="text-sm text-gray-500">
                    Abierta {{ $session->opened_at->format('H:i') }} por {{ $session->openedBy->name }}
                    @unless ($isOpen)
                        · Cerrada {{ $session->closed_at->format('d/m/Y H:i') }}
                    @endunless
                </p>
            </div>
            @if ($isOpen)
                <a href="{{ route('cash.index') }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Ir a la caja abierta
                </a>
            @endif
        </div>

        @include('cash.partials.flash')

        @unless ($isOpen)
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-lg border border-gray-200 bg-white p-4">
                    <p class="text-sm text-gray-500">Efectivo esperado</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">$ {{ number_format($expected, 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4">
                    <p class="text-sm text-gray-500">Efectivo contado</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">$ {{ number_format($session->counted_amount, 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg p-4 {{ $tone }}">
                    <p class="text-sm">Diferencia</p>
                    <p class="mt-1 text-2xl font-semibold">
                        {{ $difference > 0 ? '+' : '' }}$ {{ number_format($difference, 0, ',', '.') }}
                    </p>
                    <p class="text-sm">
                        @if ($difference === 0)
                            La caja cierra justa
                        @elseif ($difference < 0)
                            Faltante
                        @else
                            Sobrante
                        @endif
                    </p>
                </div>
            </div>

            @if ($session->notes)
                <div class="rounded-lg border border-gray-200 bg-white p-4">
                    <p class="text-sm font-medium text-gray-700">Nota del cierre</p>
                    <p class="mt-1 text-sm text-gray-600">{{ $session->notes }}</p>
                </div>
            @endif
        @endunless

        @include('cash.partials.summary', ['summary' => $summary, 'expected' => $expected])

        @include('cash.partials.movements-table', ['movements' => $movements])
    </div>
</x-gym-layout>
