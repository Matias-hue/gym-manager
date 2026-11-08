<form method="POST" action="{{ route('cash.open') }}" class="max-w-md rounded-lg border border-gray-200 bg-white p-6" data-lock-submit>
    @csrf

    <h2 class="text-lg font-medium text-gray-900">Abrir caja</h2>
    <p class="mt-1 text-sm text-gray-500">
        Ingresá el efectivo con el que arranca el día (el cambio que hay en el cajón).
    </p>

    <label for="opening_amount" class="mt-4 block text-sm font-medium text-gray-700">Fondo inicial</label>
    <div class="relative mt-1">
        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-500">$</span>
        <input
            id="opening_amount"
            name="opening_amount"
            type="number"
            inputmode="numeric"
            min="0"
            step="1"
            value="{{ old('opening_amount', 0) }}"
            required
            class="w-full rounded-md border-gray-300 pl-7 shadow-sm focus:border-gray-900 focus:ring-gray-900"
        >
    </div>
    @error('opening_amount')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror

    <button
        type="submit"
        data-loading-text="Abriendo..."
        class="mt-4 w-full rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 disabled:cursor-not-allowed disabled:opacity-60"
    >
        Abrir caja
    </button>
</form>
