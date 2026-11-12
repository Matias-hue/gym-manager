@php
    $exportKey = $tabs[$tab]['export'] ?? null;
@endphp

<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Reportes</h1>
        <p class="mt-1 text-sm text-gray-500">
            {{ $filters->gym->name }} · {{ $filters->period->label() }}
        </p>
    </div>

    <div class="no-print flex items-center gap-2">
        @if ($exportKey)
            <a
                href="{{ route('reports.export', ['report' => $exportKey] + $filters->toQuery()) }}"
                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
            >Exportar CSV</a>
        @endif

        <button
            type="button"
            data-print
            class="inline-flex items-center rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
        >Imprimir / PDF</button>
    </div>
</div>
