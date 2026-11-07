@vite(['resources/css/attendance.css', 'resources/js/attendance.js'])

<x-gym-layout>
    <div class="mx-auto max-w-3xl space-y-6 p-4 sm:p-6">

        <header>
            <h1 class="text-2xl font-bold text-gray-900">Visitas</h1>
            <p class="text-sm text-gray-600">
                Anotá a los socios que van ingresando. Buscá por documento o por nombre,
                o tocá "Anotar" en la lista de esperados.
            </p>
        </header>

        {{-- Búsqueda --}}
        <form id="attendance-form" method="POST" action="{{ route('attendances.store') }}"
              class="rounded-xl bg-white p-5 shadow">
            @csrf

            <label for="attendance-code" class="block text-sm font-medium text-gray-700">
                Documento o nombre del socio
            </label>

            <div class="relative mt-2">
                <div class="flex gap-3">
                    <input id="attendance-code" name="code" type="text"
                           autocomplete="off" autofocus required value="{{ old('code') }}"
                           data-search-url="{{ route('attendances.search') }}"
                           class="w-full rounded-lg border-gray-300 text-xl focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="Ej: 30123456 o Juan Pérez">

                    <button type="submit"
                            class="rounded-lg bg-indigo-600 px-6 py-2 font-semibold text-white hover:bg-indigo-700">
                        Anotar
                    </button>
                </div>

                {{-- Sugerencias en vivo (las llena attendance.js) --}}
                <div id="attendance-suggestions"
                     class="absolute z-10 mt-1 hidden w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg"></div>
            </div>

            @error('code')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </form>

        {{-- Formulario oculto: lo usa el JS al tocar una sugerencia --}}
        <form id="attendance-pick-form" method="POST" action="{{ route('attendances.store') }}" class="hidden">
            @csrf
            <input type="hidden" name="member_id" value="">
        </form>

        {{-- Plantillas que clona attendance.js --}}
        <template id="attendance-suggestion-template">
            <button type="button"
                    class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left hover:bg-indigo-50 focus:bg-indigo-50 focus:outline-none">
                <span class="font-medium text-gray-900" data-name></span>
                <span class="flex items-center gap-2 text-sm text-gray-500">
                    <span data-document></span>
                    <span data-badge class="hidden rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">
                        ya anotado
                    </span>
                </span>
            </button>
        </template>

        <template id="attendance-empty-template">
            <p class="px-4 py-3 text-sm text-gray-500">Sin coincidencias</p>
        </template>

        @if ($notice)
            <div class="rounded-xl border border-gray-300 bg-gray-50 p-4 text-sm text-gray-800" role="status">
                {{ $notice }}
            </div>
        @endif

        {{-- Varias coincidencias al enviar el formulario sin elegir una sugerencia --}}
        @if ($matches)
            <section class="rounded-xl border-2 border-indigo-200 bg-indigo-50 p-5">
                <h2 class="font-semibold text-indigo-900">
                    Hay varios socios que coinciden. ¿A quién anotamos?
                </h2>

                <ul class="mt-3 space-y-2">
                    @foreach ($matches as $match)
                        <li>
                            <form method="POST" action="{{ route('attendances.store') }}">
                                @csrf
                                <input type="hidden" name="member_id" value="{{ $match['id'] }}">
                                <button type="submit"
                                        class="flex w-full items-center justify-between rounded-lg bg-white px-4 py-3 text-left shadow-sm hover:bg-indigo-100">
                                    <span class="font-medium text-gray-900">{{ $match['name'] }}</span>
                                    <span class="text-sm text-gray-500">DNI {{ $match['document'] }}</span>
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($result)
            <x-attendance-result :result="$result" />
        @endif

        @include('attendances.partials.expected', ['expected' => $expected])

        @include('attendances.partials.today', ['attendances' => $attendances])
    </div>
</x-gym-layout>