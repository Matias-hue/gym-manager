{{-- Búsqueda de socio por documento o nombre, con sugerencias en vivo (attendance.js) --}}
<form
    id="attendance-form"
    method="POST"
    action="{{ route('attendances.store') }}"
    class="rounded-xl border border-stone-200 bg-white p-5"
>
    @csrf

    <label for="attendance-code" class="block text-sm font-medium text-stone-700">
        Documento o nombre del socio
    </label>

    <div class="relative mt-2">
        <div class="flex flex-col gap-3 sm:flex-row">
            <input
                id="attendance-code"
                name="code"
                type="text"
                autocomplete="off"
                required
                value="{{ old('code') }}"
                data-search-url="{{ route('attendances.search') }}"
                placeholder="Ej: 30123456 o Juan Pérez"
                class="min-h-11 w-full rounded-lg border-stone-300 text-lg focus:border-lime-500 focus:ring-lime-400"
            >

            <button
                type="submit"
                class="inline-flex min-h-11 items-center justify-center rounded-lg bg-lime-400 px-6 text-sm font-semibold text-stone-950 transition hover:bg-lime-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-lime-400 focus-visible:ring-offset-2"
            >
                Anotar
            </button>
        </div>

        {{-- Sugerencias en vivo (las llena attendance.js) --}}
        <div
            id="attendance-suggestions"
            class="absolute z-10 mt-1 hidden w-full overflow-hidden rounded-lg border border-stone-200 bg-white shadow-lg"
        ></div>
    </div>

    @error('code')
        <p role="alert" class="mt-2 text-sm text-red-700">{{ $message }}</p>
    @enderror
</form>

{{-- Formulario oculto: lo usa el JS al tocar una sugerencia --}}
<form id="attendance-pick-form" method="POST" action="{{ route('attendances.store') }}" class="hidden">
    @csrf
    <input type="hidden" name="member_id" value="">
</form>

{{-- Plantillas que clona attendance.js --}}
<template id="attendance-suggestion-template">
    <button
        type="button"
        class="flex min-h-11 w-full items-center justify-between gap-3 px-4 py-2 text-left transition hover:bg-stone-50 focus:bg-stone-50 focus:outline-none"
    >
        <span class="font-medium text-stone-900" data-name></span>

        <span class="flex items-center gap-2 text-sm text-stone-500">
            <span data-document></span>
            <span data-badge class="hidden rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">
                ya anotado
            </span>
        </span>
    </button>
</template>

<template id="attendance-empty-template">
    <p class="px-4 py-3 text-sm text-stone-500">Sin coincidencias</p>
</template>
