<form
    method="POST"
    action="{{ route('cash.close', $session) }}"
    class="rounded-lg border border-gray-200 bg-white p-6"
    data-cash-close
    data-lock-submit
    data-expected="{{ $summary['expected'] }}"
>
    @csrf

    <h2 class="text-lg font-medium text-gray-900">Cerrar caja</h2>
    <p class="mt-1 text-sm text-gray-500">
        Contá el efectivo que hay en el cajón y cargalo. Se compara contra lo esperado.
    </p>

    <div class="mt-4 space-y-4">
        <div>
            <label for="counted_amount" class="block text-sm font-medium text-gray-700">Efectivo contado</label>
            <div class="relative mt-1">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-500">$</span>
                <input
                    id="counted_amount"
                    name="counted_amount"
                    type="number"
                    inputmode="numeric"
                    min="0"
                    step="1"
                    value="{{ old('counted_amount') }}"
                    required
                    data-counted-input
                    class="w-full rounded-md border-gray-300 pl-7 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                >
            </div>
            @error('counted_amount')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="rounded-md bg-gray-50 px-4 py-3 text-gray-500" data-difference-box>
            <p class="text-xs uppercase tracking-wide" data-difference-label>Diferencia</p>
            <p class="text-xl font-semibold" data-difference-amount>Ingresá el efectivo contado</p>
        </div>

        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">Nota (opcional)</label>
            <textarea
                id="notes"
                name="notes"
                rows="2"
                maxlength="500"
                class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
            >{{ old('notes') }}</textarea>
            @error('notes')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <button
        type="submit"
        data-loading-text="Cerrando..."
        class="mt-4 w-full rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
    >
        Cerrar caja
    </button>
</form>
