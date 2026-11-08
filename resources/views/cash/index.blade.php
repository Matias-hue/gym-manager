<x-gym-layout>
    <div class="mx-auto max-w-5xl space-y-6 p-4 sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-gray-900">Caja del día</h1>
            @if ($current)
                <span class="inline-flex items-center gap-2 rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800">
                    <span class="h-2 w-2 rounded-full bg-green-500"></span>
                    Abierta desde {{ $current->opened_at->format('d/m H:i') }} por {{ $current->openedBy->name }}
                </span>
            @else
                <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-700">
                    <span class="h-2 w-2 rounded-full bg-gray-400"></span>
                    Caja cerrada
                </span>
            @endif
        </div>

        @include('cash.partials.flash')

        @if ($current)
            @include('cash.partials.summary', ['summary' => $summary])

            <div class="grid gap-6 lg:grid-cols-2">
                @include('cash.partials.movement-form', ['session' => $current])
                @include('cash.partials.close-form', ['session' => $current, 'summary' => $summary])
            </div>

            @include('cash.partials.movements-table', ['movements' => $movements])
        @else
            @include('cash.partials.open-form')
        @endif

        @include('cash.partials.history', ['history' => $history])
    </div>

    @include('cash.partials.confirm-modal')
</x-gym-layout>
