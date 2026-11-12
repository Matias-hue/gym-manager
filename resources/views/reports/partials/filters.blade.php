@use('App\Services\Reports\ReportPeriod')

<form
    method="GET"
    action="{{ route('reports.index') }}"
    class="no-print space-y-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm"
    data-report-filters
>
    <input type="hidden" name="tab" value="{{ $tab }}">

    {{-- Rangos rápidos --}}
    <div class="flex flex-wrap gap-2">
        @foreach ($ranges as $key => $label)
            @php $active = $filters->period->equals(ReportPeriod::preset($key)); @endphp
            <a
                href="{{ route('reports.index', ['tab' => $tab, 'range' => $key] + $filters->extraQuery()) }}"
                @class([
                    'rounded-full px-3 py-1.5 text-sm font-medium transition',
                    'bg-indigo-600 text-white shadow-sm' => $active,
                    'bg-gray-100 text-gray-700 hover:bg-gray-200' => ! $active,
                ])
                @if ($active) aria-current="true" @endif
            >{{ $label }}</a>
        @endforeach
    </div>

    {{-- Rango personalizado y filtros de la pestaña --}}
    <div class="flex flex-wrap items-end gap-3">
        <div>
            <label for="report-from" class="block text-xs font-medium text-gray-500">Desde</label>
            <input
                type="date"
                id="report-from"
                name="from"
                value="{{ $filters->period->from->toDateString() }}"
                class="mt-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
        </div>

        <div>
            <label for="report-to" class="block text-xs font-medium text-gray-500">Hasta</label>
            <input
                type="date"
                id="report-to"
                name="to"
                value="{{ $filters->period->to->toDateString() }}"
                class="mt-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
        </div>

        @if ($tab === 'income')
            <div>
                <label for="report-method" class="block text-xs font-medium text-gray-500">Método de pago</label>
                <select
                    id="report-method"
                    name="method"
                    data-auto-submit
                    class="mt-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">Todos</option>
                    @foreach ($methods as $key => $label)
                        <option value="{{ $key }}" @selected($filters->method === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="report-plan" class="block text-xs font-medium text-gray-500">Plan</label>
                <select
                    id="report-plan"
                    name="plan_id"
                    data-auto-submit
                    class="mt-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">Todos</option>
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}" @selected($filters->planId === $plan->id)>{{ $plan->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <button
            type="submit"
            class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-1"
        >Aplicar</button>
    </div>

    @if ($errors->any())
        <ul class="space-y-1 text-sm text-rose-600" role="alert">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif
</form>
