{{--
    Formulario compartido de alta y edición.
    Recibe: $action, $method ('POST' o 'PUT'), $submitLabel y $member (null al dar de alta).
--}}
@php
    $member = $member ?? null;
    $duplicate = session('duplicate');
    $extraFields = ['document_number', 'email', 'birth_date', 'emergency_contact', 'medical_notes'];
    $extraOpen = $errors->hasAny($extraFields)
        || ($member && collect($extraFields)->contains(fn ($f) => filled($member->{$f})));
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6" novalidate>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    {{-- Aviso de posible duplicado: no bloquea, solo pregunta --}}
    @if ($duplicate)
        <div role="alert" class="rounded-lg border border-amber-300 bg-amber-50 p-4">
            <p class="text-sm font-semibold text-amber-950">
                Ya hay un socio llamado {{ $duplicate['name'] }}.
            </p>
            <p class="mt-1 text-sm text-amber-900">
                Si es la misma persona no hace falta cargarla de nuevo. Si es otra persona con el mismo nombre, podés guardarla igual.
            </p>

            <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                <a
                    href="{{ route('members.show', $duplicate['id']) }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm font-semibold text-stone-950 transition hover:bg-stone-50"
                >
                    Ver la ficha existente
                </a>

                <button
                    type="submit"
                    name="confirm_duplicate"
                    value="1"
                    class="inline-flex min-h-11 items-center justify-center rounded-lg border border-amber-700 bg-amber-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-800"
                >
                    Es otra persona, guardar igual
                </button>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <x-field name="first_name" label="Nombre" :value="$member?->first_name" required autocomplete="given-name" />
        <x-field name="last_name" label="Apellido" :value="$member?->last_name" required autocomplete="family-name" />

        <div class="sm:col-span-2">
            <x-field
                name="phone"
                label="Celular"
                type="tel"
                inputmode="numeric"
                :value="$member?->phone"
                placeholder="3764123456"
                hint="Con código de área y número, sin 0 ni 15. Se usa para avisar por WhatsApp."
                autocomplete="tel-national"
            />
        </div>
    </div>

    @if ($member)
        <div>
            <label for="status" class="block text-sm font-medium text-stone-700">Estado del socio</label>
            <select
                id="status"
                name="status"
                class="mt-1.5 w-full rounded-lg border border-stone-200 bg-white px-3.5 py-2.5 text-sm text-stone-900 outline-none transition focus:border-stone-400 focus:ring-2 focus:ring-lime-200 sm:w-64"
            >
                @foreach (['active' => 'Activo', 'inactive' => 'Inactivo (dejó de venir)', 'suspended' => 'Suspendido'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $member->status) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status')
                <p class="mt-1.5 text-xs font-medium text-red-700">{{ $message }}</p>
            @enderror
            <p class="mt-1.5 text-xs text-stone-500">
                Esto es distinto de "al día" o "vencido", que se calcula solo según los pagos.
            </p>
        </div>
    @endif

    {{-- Datos que no hacen falta con el cliente enfrente: se completan cuando se pueda --}}
    <details class="group rounded-lg border border-stone-200 bg-stone-50/60" @if ($extraOpen) open @endif>
        <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between gap-3 px-4 py-2.5 text-sm font-semibold text-stone-800">
            Más datos (opcional)
            <svg viewBox="0 0 24 24" class="h-4 w-4 text-stone-500 transition group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="m6 9 6 6 6-6"/>
            </svg>
        </summary>

        <div class="grid grid-cols-1 gap-5 border-t border-stone-200 p-4 sm:grid-cols-2">
            <x-field
                name="document_number"
                label="DNI"
                inputmode="numeric"
                :value="$member?->document_number"
                hint="Solo si el gimnasio lo usa. No es obligatorio."
            />

            <x-field name="email" label="Email" type="email" :value="$member?->email" autocomplete="email" />

            <x-field
                name="birth_date"
                label="Fecha de nacimiento"
                type="date"
                :value="$member?->birth_date?->format('Y-m-d')"
                autocomplete="bday"
            />

            <x-field name="emergency_contact" label="Contacto de emergencia" :value="$member?->emergency_contact" placeholder="Nombre y teléfono" />

            <div class="sm:col-span-2">
                <label for="medical_notes" class="block text-sm font-medium text-stone-700">
                    Observaciones
                    <span class="font-normal text-stone-400">(opcional)</span>
                </label>
                <textarea
                    id="medical_notes"
                    name="medical_notes"
                    rows="3"
                    placeholder="Lesiones, indicaciones del médico, algo que recepción tenga que saber"
                    class="mt-1.5 w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-stone-900 outline-none transition placeholder:text-stone-400 focus:border-stone-400 focus:ring-2 focus:ring-lime-200 {{ $errors->has('medical_notes') ? 'border-red-400' : 'border-stone-200' }}"
                >{{ old('medical_notes', $member?->medical_notes) }}</textarea>
                @error('medical_notes')
                    <p class="mt-1.5 text-xs font-medium text-red-700">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </details>

    <div class="flex flex-col-reverse gap-2 border-t border-stone-200 pt-6 sm:flex-row sm:justify-end">
        <a
            href="{{ $member ? route('members.show', $member) : route('members.index') }}"
            class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm font-semibold text-stone-950 transition hover:bg-stone-50"
        >
            Cancelar
        </a>

        <button
            type="submit"
            class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-950 bg-stone-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-stone-800 active:scale-[0.98]"
        >
            {{ $submitLabel }}
        </button>
    </div>
</form>
