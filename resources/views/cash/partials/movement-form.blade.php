<form method="POST" action="{{ route('cash.movements.store', $session) }}" class="rounded-lg border border-gray-200 bg-white p-6" data-lock-submit>
    @csrf

    <h2 class="text-lg font-medium text-gray-900">Registrar movimiento</h2>
    <p class="mt-1 text-sm text-gray-500">
        Gastos o ingresos que no son cuotas, por ejemplo: compra de agua, limpieza, venta de una botella.
    </p>

    <div class="mt-4 space-y-4">
        <div>
            <label for="type" class="block text-sm font-medium text-gray-700">Tipo</label>
            <select id="type" name="type" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                <option value="expense" @selected(old('type', 'expense') === 'expense')>Egreso (sale plata)</option>
                <option value="income" @selected(old('type') === 'income')>Ingreso (entra plata)</option>
            </select>
            @error('type')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="concept" class="block text-sm font-medium text-gray-700">Concepto</label>
            <input
                id="concept"
                name="concept"
                type="text"
                maxlength="150"
                value="{{ old('concept') }}"
                required
                class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
            >
            @error('concept')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="amount" class="block text-sm font-medium text-gray-700">Monto</label>
            <div class="relative mt-1">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-500">$</span>
                <input
                    id="amount"
                    name="amount"
                    type="number"
                    inputmode="numeric"
                    min="1"
                    step="1"
                    value="{{ old('amount') }}"
                    required
                    class="w-full rounded-md border-gray-300 pl-7 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                >
            </div>
            @error('amount')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <button
        type="submit"
        data-loading-text="Guardando..."
        class="mt-4 w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60"
    >
        Guardar movimiento
    </button>
</form>
