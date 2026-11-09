@php
    $input = 'mt-1.5 w-full rounded-lg border border-stone-200 bg-white px-3.5 py-2.5 text-sm text-stone-900 outline-none transition placeholder:text-stone-400 focus:border-stone-400 focus:ring-2 focus:ring-lime-200';
    $label = 'block text-sm font-medium text-stone-700';
@endphp

<x-gym-layout title="Cargar varios socios">
    <section
        class="min-h-[calc(100vh-3.5rem)] w-full px-6 py-9 lg:px-10 lg:py-10 xl:px-14"
        aria-labelledby="bulk-title"
    >
        <div class="mx-auto max-w-5xl">

            <a href="{{ route('members.index') }}" class="inline-flex min-h-11 items-center text-sm font-medium text-stone-500 transition hover:text-stone-900">
                ← Volver a socios
            </a>

            <header class="mt-2 border-b border-stone-200 pb-6">
                <h1 id="bulk-title" class="font-heading text-3xl font-semibold tracking-tight text-stone-950">
                    Cargar varios socios
                </h1>
                <p class="mt-2 text-sm text-stone-500">
                    Pasá el cuaderno de corrido: escribí y apretá Enter para ir al siguiente campo. Con nombre y apellido alcanza; lo demás es opcional.
                </p>
            </header>

            @if (session('status'))
                <div role="status" class="mt-4 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="mt-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                    <ul class="list-disc space-y-1 pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('members.bulk.store') }}"
                id="bulk-form"
                data-next-index="{{ max($rowIndexes) + 1 }}"
                class="mt-6 space-y-6"
                novalidate
            >
                @csrf

                {{-- Aviso de nombres repetidos: no bloquea, solo pregunta --}}
                @if (! empty($duplicates))
                    <div role="alert" class="rounded-lg border border-amber-300 bg-amber-50 p-4">
                        <p class="text-sm font-semibold text-amber-950">
                            Hay {{ count($duplicates) }} {{ count($duplicates) === 1 ? 'nombre repetido' : 'nombres repetidos' }}: {{ implode(', ', $duplicates) }}.
                        </p>
                        <p class="mt-1 text-sm text-amber-900">
                            Todavía no se guardó nada. Si son personas distintas, guardá igual. Si es la misma, quitá esa fila.
                        </p>

                        <button
                            type="submit"
                            name="confirm_duplicates"
                            value="1"
                            class="mt-3 inline-flex min-h-11 items-center justify-center rounded-lg border border-amber-700 bg-amber-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-800"
                        >
                            Son otras personas, guardar igual
                        </button>
                    </div>
                @endif

                {{-- Valores para todos: se pueden cambiar fila por fila --}}
                <div class="rounded-xl border border-stone-200 bg-white p-5 sm:p-6">
                    <h2 class="text-sm font-semibold text-stone-950">Para todos los socios de la lista</h2>
                    <p class="mt-1 text-xs text-stone-500">
                        Si completás el vencimiento, cada socio queda con su cuota cargada. Si lo dejás vacío, quedan "sin pagos" hasta que paguen.
                    </p>

                    <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="plan_id" class="{{ $label }}">Plan</label>
                            <select name="plan_id" id="plan_id" class="{{ $input }}">
                                <option value="">Sin plan</option>
                                @foreach ($plans as $plan)
                                    <option
                                        value="{{ $plan->id }}"
                                        data-capacity="{{ $plan->capacity_per_slot }}"
                                        @selected((string) old('plan_id') === (string) $plan->id)
                                    >{{ $plan->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="default_ends_on" class="{{ $label }}">
                                Vence el
                                <span class="font-normal text-stone-400">(opcional)</span>
                            </label>
                            <input
                                type="text"
                                inputmode="numeric"
                                name="default_ends_on"
                                id="default_ends_on"
                                value="{{ old('default_ends_on') }}"
                                placeholder="15/11"
                                autocomplete="off"
                                class="{{ $input }}"
                            >
                            <p class="mt-1.5 text-xs text-stone-500">Día y mes alcanza: 15/11. El año se completa solo.</p>
                        </div>
                    </div>
                </div>

                {{-- Tabla de filas --}}
                <div class="overflow-hidden rounded-xl border border-stone-200 bg-white">
                    <div class="overflow-x-auto">
                        <table id="rows-table" class="w-full min-w-[56rem] border-collapse text-left">
                            <thead class="border-b border-stone-200 bg-stone-50 text-xs font-semibold text-stone-600">
                                <tr>
                                    <th scope="col" class="py-2.5 pl-3 pr-1">Nombre</th>
                                    <th scope="col" class="px-1 py-2.5">Apellido</th>
                                    <th scope="col" class="px-1 py-2.5">Celular</th>
                                    <th scope="col" class="px-1 py-2.5">Plan</th>
                                    <th scope="col" class="px-1 py-2.5">Turno</th>
                                    <th scope="col" class="px-1 py-2.5">Vence el</th>
                                    <th scope="col" class="w-12 py-2.5 pl-1 pr-3"><span class="sr-only">Quitar</span></th>
                                </tr>
                            </thead>

                            @foreach ($rowIndexes as $i)
                                @include('members._bulk_row', [
                                    'i' => $i,
                                    'plans' => $plans,
                                    'messages' => $rowErrors[$i] ?? [],
                                    'duplicate' => isset($duplicates[$i]),
                                ])
                            @endforeach
                        </table>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-stone-200 bg-stone-50 px-4 py-3">
                        <button
                            type="button"
                            id="add-row"
                            class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm font-semibold text-stone-950 transition hover:bg-stone-50"
                        >
                            + Agregar fila
                        </button>
                        <p class="text-xs text-stone-500"><span id="row-count">0</span> filas</p>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-stone-200 pt-6 sm:flex-row sm:justify-end">
                    <a
                        href="{{ route('members.index') }}"
                        class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm font-semibold text-stone-950 transition hover:bg-stone-50"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        id="submit-btn"
                        class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-950 bg-stone-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-stone-800 active:scale-[0.98] disabled:opacity-60"
                    >
                        Guardar socios
                    </button>
                </div>
            </form>

            {{-- Molde para las filas nuevas que agrega el JavaScript --}}
            <template id="row-template">
                @include('members._bulk_row', [
                    'i' => '__INDEX__',
                    'plans' => $plans,
                    'messages' => [],
                    'duplicate' => false,
                ])
            </template>

        </div>
    </section>

    @vite('resources/js/members/bulk.js')
</x-gym-layout>
