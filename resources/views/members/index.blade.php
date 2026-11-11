<x-gym-layout title="Socios">
    <section
        class="min-h-[calc(100vh-3.5rem)] w-full px-6 py-9 lg:px-10 lg:py-10 xl:px-14"
        aria-labelledby="members-title"
    >
        <div class="mx-auto max-w-7xl">

            {{-- ENCABEZADO --}}
            <header class="flex flex-col gap-5 border-b border-stone-200 pb-7 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-stone-500">
                        Recepción
                    </p>
                    <h1 id="members-title" class="mt-1 font-heading text-3xl font-semibold tracking-tight text-stone-950 sm:text-4xl">
                        Socios
                    </h1>
                </div>

                <div>
                    <a
                        href="{{ route('members.create') }}"
                        class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-950 bg-stone-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-stone-800 active:scale-[0.98]"
                    >
                        Nuevo socio
                    </a>

                    <a href="{{ route('members.bulk.create') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm font-semibold text-stone-950 transition hover:bg-stone-50">
                        Cargar varios
                    </a>
                    </div>
            </header>

            {{-- BUSCADOR Y FILTROS --}}
            <div class="mt-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                <form method="GET" action="{{ route('members.index') }}" role="search" class="w-full lg:max-w-sm">
                    @if (request()->has('estado'))
                        <input type="hidden" name="estado" value="{{ $estado }}">
                    @endif

                    <label class="relative flex items-center">
                        <span class="sr-only">Buscar socio por nombre o celular</span>

                        <svg viewBox="0 0 24 24" fill="none" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" aria-hidden="true">
                            <circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="1.7"/>
                            <path d="m16 16 4.2 4.2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                        </svg>

                        <input
                            type="search"
                            name="q"
                            value="{{ $search }}"
                            data-live-search
                            autocomplete="off"
                            placeholder="Buscar por nombre o celular"
                            class="w-full rounded-lg border border-stone-200 bg-white py-3 pl-10 pr-4 text-sm text-stone-900 outline-none transition placeholder:text-stone-400 focus:border-stone-400 focus:ring-2 focus:ring-lime-200"
                        />
                    </label>
                </form>

                <nav class="flex flex-wrap gap-2" aria-label="Filtrar socios por estado">
                    @php
                        $tabs = [
                            'active'    => ['Activos', $counts['active'] ?? 0],
                            'inactive'  => ['Inactivos', $counts['inactive'] ?? 0],
                            'suspended' => ['Suspendidos', $counts['suspended'] ?? 0],
                            'all'       => ['Todos', $counts->sum()],
                        ];
                    @endphp

                    @foreach ($tabs as $key => [$tabLabel, $tabCount])
                        <a
                            href="{{ route('members.index', array_filter(['q' => $search, 'estado' => $key])) }}"
                            @if ($estado === $key) aria-current="true" @endif
                            class="rounded-md px-3 py-2 text-xs font-semibold transition {{ $estado === $key ? 'bg-stone-950 text-white' : 'border border-stone-200 text-stone-600 hover:bg-stone-50' }}"
                        >
                            {{ $tabLabel }} · {{ $tabCount }}
                        </a>
                    @endforeach
                </nav>
            </div>

            {{-- LISTA --}}
            <div class="mt-6 rounded-xl border border-stone-200 bg-white p-5 sm:p-6">

                @if ($members->isEmpty())
                    <div class="py-10 text-center">
                        @if ($search !== '')
                            <p class="text-sm font-medium text-stone-900">Ningún socio coincide con "{{ $search }}".</p>
                            <p class="mt-1 text-sm text-stone-500">Probá con otra parte del nombre o del celular.</p>
                            <a href="{{ route('members.create') }}" class="mt-4 inline-flex min-h-11 items-center rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm font-semibold text-stone-950 transition hover:bg-stone-50">
                                Cargar como socio nuevo
                            </a>
                        @else
                            <p class="text-sm font-medium text-stone-900">Todavía no hay socios en esta lista.</p>
                            <p class="mt-1 text-sm text-stone-500">Empezá cargando a los socios actuales del cuaderno.</p>
                            <a href="{{ route('members.create') }}" class="mt-4 inline-flex min-h-11 items-center rounded-lg border border-stone-950 bg-stone-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-stone-800">
                                Nuevo socio
                            </a>
                        @endif
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[720px] text-left text-sm">
                            <caption class="sr-only">Lista de socios</caption>

                            <thead class="border-b border-stone-200 text-xs font-medium text-stone-500">
                                <tr>
                                    <th scope="col" class="pb-3 pr-4">Socio</th>
                                    <th scope="col" class="pb-3 pr-4">Plan</th>
                                    <th scope="col" class="pb-3 pr-4">Vencimiento</th>
                                    <th scope="col" class="pb-3 pr-4">Estado</th>
                                    <th scope="col" class="pb-3">Acciones</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-stone-100">
                                @foreach ($members as $member)
                                    @php
                                        $membership = $member->currentMembership;
                                        $state = $member->state();
                                    @endphp

                                    <tr>
                                        <td class="py-3.5 pr-4 font-medium">
                                            <a href="{{ route('members.show', $member) }}" class="transition hover:underline">
                                                {{ $member->full_name }}
                                            </a>

                                            @if ($member->status !== 'active')
                                                <span class="ml-1.5 rounded-full bg-stone-100 px-2 py-0.5 text-xs font-medium text-stone-600">
                                                    {{ $member->status === 'inactive' ? 'Inactivo' : 'Suspendido' }}
                                                </span>
                                            @endif

                                            @if ($member->phone || $member->document_number)
                                                <span class="block text-xs font-normal text-stone-400">
                                                    {{ $member->phone }}
                                                    @if ($member->phone && $member->document_number) · @endif
                                                    @if ($member->document_number) DNI {{ $member->document_number }} @endif
                                                </span>
                                            @endif
                                        </td>

                                        <td class="py-3.5 pr-4 text-stone-600">
                                            {{ $membership?->plan?->name ?? '—' }}
                                            @if ($membership?->time_slot)
                                                <span class="block text-xs text-stone-400">Turno {{ $membership->time_slot }}</span>
                                            @endif
                                        </td>

                                        <td class="py-3.5 pr-4 text-stone-600">
                                            {{ $membership ? $membership->ends_on->isoFormat('D MMM YYYY') : '—' }}
                                        </td>

                                        <td class="py-3.5 pr-4">
                                            @include('members._state', ['state' => $state])
                                        </td>

                                        <td class="py-3.5">
                                            <div class="flex flex-wrap gap-2">
                                                <a href="{{ route('members.show', $member) }}" class="inline-flex min-h-11 items-center rounded-md border border-stone-300 bg-white px-3 py-2 text-xs font-semibold text-stone-950 transition hover:bg-stone-50">
                                                    Ver ficha
                                                </a>

                                                @if (\Illuminate\Support\Facades\Route::has('payments.create'))
                                                    <a href="{{ route('payments.create', $member) }}" class="inline-flex min-h-11 items-center rounded-md border border-stone-950 bg-stone-950 px-3 py-2 text-xs font-semibold text-white transition hover:bg-stone-800">
                                                        Registrar pago
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-5">
                        {{ $members->links() }}
                    </div>
                @endif

            </div>

        </div>
    </section>

    @push('scripts')
        @vite('resources/js/members.js')
    @endpush
</x-gym-layout>
