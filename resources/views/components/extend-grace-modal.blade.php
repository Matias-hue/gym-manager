{{-- Modal único de prórroga. Se incluye una sola vez por pantalla (ej. en dues/index). --}}
<div id="extend-grace-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" aria-hidden="true">
    <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl" role="dialog" aria-modal="true" aria-labelledby="extend-grace-title">
        <h2 id="extend-grace-title" class="text-lg font-semibold text-gray-900">Dar prórroga</h2>
        <p class="mt-1 text-sm text-gray-600">
            Socio: <span id="extend-grace-member" class="font-medium"></span><br>
            Vencimiento de la cuota: <span id="extend-grace-ends-on" class="font-medium"></span>
        </p>

        <form id="extend-grace-form" method="POST" action="" class="mt-4 space-y-4">
            @csrf

            <div>
                <label for="grace_until" class="block text-sm font-medium text-gray-700">Puede pagar hasta</label>
                <input
                    type="date"
                    id="grace_until"
                    name="grace_until"
                    value="{{ old('grace_until') }}"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                @error('grace_until')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="extend_notes" class="block text-sm font-medium text-gray-700">Nota</label>
                <textarea
                    id="extend_notes"
                    name="notes"
                    rows="3"
                    maxlength="500"
                    required
                    placeholder="Ej: avisó que cobra el viernes"
                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" data-extend-grace-close class="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Guardar prórroga
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Si la validación falló, reabrimos el modal para mostrar los errores --}}
@if ($errors->has('grace_until') || $errors->has('notes'))
    <script>window.reopenExtendGrace = true;</script>
@endif