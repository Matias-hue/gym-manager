@php
    $tabs = [
        'pendientes' => 'Pendientes',
        'por_vencer' => 'Por vencer',
        'gracia' => 'En gracia',
        'vencidos' => 'Vencidos',
        'sin_pagos' => 'Sin pagos',
        'todos' => 'Todos',
    ];
@endphp

<x-gym-layout title="Vencimientos">
    <x-extend-grace-modal />
    <section
        class="min-h-[calc(100vh-3.5rem)] w-full px-6 py-9 lg:px-10 lg:py-10 xl:px-14"
        aria-labelledby="dues-title"
    >
        <div class="mx-auto max-w-4xl">

            <header class="border-b border-stone-200 pb-6">
                <h1 id="dues-title" class="font-heading text-3xl font-semibold tracking-tight text-stone-950">
                    Vencimientos
                </h1>
                <p class="mt-2 text-sm text-stone-500">
                    Quién vence en los próximos {{ $expiringDays }} días y quién ya debe. Tocá "Avisar" para abrir WhatsApp con el mensaje escrito.
                </p>
            </header>

            @if (session('status'))
                <div role="status" class="mt-4 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div role="alert" class="mt-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Buscador --}}
            <form method="GET" action="{{ route('dues.index') }}" class="mt-6 flex flex-col gap-2 sm:flex-row">
                <input type="hidden" name="estado" value="{{ $filter }}">
                <label for="q" class="sr-only">Buscar socio</label>
                <input
                    type="search"
                    id="q"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Buscar por nombre, DNI o celular"
                    class="min-h-11 w-full rounded-lg border border-stone-200 bg-white px-3.5 py-2.5 text-sm text-stone-900 outline-none transition placeholder:text-stone-400 focus:border-stone-400 focus:ring-2 focus:ring-lime-200"
                >
                <button
                    type="submit"
                    class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-950 bg-stone-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-stone-800 active:scale-[0.98]"
                >
                    Buscar
                </button>
            </form>

            {{-- Pestañas por estado --}}
            <nav class="-mx-6 mt-4 flex gap-2 overflow-x-auto px-6 pb-1 sm:mx-0 sm:flex-wrap sm:px-0" aria-label="Filtrar por estado">
                @foreach ($tabs as $key => $label)
                    @php $active = $filter === $key; @endphp
                    <a
                        href="{{ route('dues.index', array_filter(['estado' => $key, 'q' => $search])) }}"
                        @if ($active) aria-current="page" @endif
                        class="inline-flex min-h-11 shrink-0 items-center gap-2 rounded-lg border px-3.5 py-2 text-sm font-medium transition
                            {{ $active ? 'border-stone-950 bg-stone-950 text-white' : 'border-stone-200 bg-white text-stone-700 hover:bg-stone-50' }}"
                    >
                        {{ $label }}
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $active ? 'bg-white/20 text-white' : 'bg-stone-100 text-stone-600' }}">
                            {{ $counts[$key] }}
                        </span>
                    </a>
                @endforeach
            </nav>

            {{-- Lista --}}
            <div class="mt-4 overflow-hidden rounded-xl border border-stone-200 bg-white">
                @forelse ($rows as $row)
                    @include('dues._row', ['row' => $row])
                @empty
                    <p class="px-5 py-10 text-center text-sm text-stone-500">
                        @if ($search !== '')
                            No hay socios que coincidan con "{{ $search }}" en esta pestaña.
                        @elseif ($filter === 'pendientes')
                            No hay nadie por vencer ni con deuda. 🎉
                        @else
                            No hay socios en esta pestaña.
                        @endif
                    </p>
                @endforelse
            </div>

        </div>
    </section>
</x-gym-layout>
