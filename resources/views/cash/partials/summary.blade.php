@php
    $expected = $expected ?? $summary['expected'];
@endphp

<div class="rounded-lg border border-gray-200 bg-white p-6">
    <h2 class="text-lg font-medium text-gray-900">Resumen del efectivo</h2>

    <dl class="mt-4 divide-y divide-gray-100 text-sm">
        <div class="flex justify-between py-2">
            <dt class="text-gray-600">Fondo inicial</dt>
            <dd class="font-medium text-gray-900">$ {{ number_format($summary['opening'], 0, ',', '.') }}</dd>
        </div>
        <div class="flex justify-between py-2">
            <dt class="text-gray-600">Ingresos en efectivo</dt>
            <dd class="font-medium text-green-700">+ $ {{ number_format($summary['cash_income'], 0, ',', '.') }}</dd>
        </div>
        <div class="flex justify-between py-2">
            <dt class="text-gray-600">Egresos</dt>
            <dd class="font-medium text-red-700">- $ {{ number_format($summary['expenses'], 0, ',', '.') }}</dd>
        </div>
        <div class="flex justify-between py-3 text-base">
            <dt class="font-semibold text-gray-900">Efectivo esperado en el cajón</dt>
            <dd class="font-semibold text-gray-900">$ {{ number_format($expected, 0, ',', '.') }}</dd>
        </div>
    </dl>

    @if ($summary['other_methods']->isNotEmpty())
        <div class="mt-4 rounded-md bg-gray-50 p-4">
            <p class="text-sm font-medium text-gray-700">Cobrado por otros medios (no entra al cajón)</p>
            <ul class="mt-2 space-y-1 text-sm text-gray-600">
                @foreach ($summary['other_methods'] as $method => $total)
                    <li class="flex justify-between">
                        <span>{{ \App\Models\CashSession::METHOD_LABELS[$method] ?? $method }}</span>
                        <span>$ {{ number_format($total, 0, ',', '.') }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
