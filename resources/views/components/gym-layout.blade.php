@props(['title' => null])

@php
    $user = auth()->user();
    $isOwner = $user?->role === 'owner';
    $roleLabel = ['owner' => 'Dueño', 'reception' => 'Recepción'][$user?->role] ?? $user?->role;
    $gymName = $user?->gym?->name ?? config('app.name', 'Gimnasio');
    $initials = mb_strtoupper(
        \Illuminate\Support\Str::of($user?->name ?? '')
            ->explode(' ')
            ->take(2)
            ->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))
            ->implode('')
    );

    $modules = collect(config('modules', []));

    // Íconos del menú (trazos de 24x24)
    $icons = [
        'home'     => '<path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/>',
        'users'    => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6"/><path d="M16 4.6a3.5 3.5 0 0 1 0 6.8M18 14.4c2 .7 3.5 2.5 3.5 5.6"/>',
        'cash'     => '<rect x="2.5" y="6" width="19" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/>',
        'check'    => '<path d="M4 12.5l5 5L20 6.5"/>',
        'calendar' => '<rect x="3.5" y="5" width="17" height="15" rx="2"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
        'bank'     => '<path d="M3 10l9-6 9 6"/><path d="M5 10v8M10 10v8M14 10v8M19 10v8M3 20h18"/>',
        'chart'    => '<path d="M5 20V10M12 20V4M19 20v-7"/>',
        'dumbbell' => '<path d="M6.5 6.5v11M17.5 6.5v11M3 9.5v5M21 9.5v5M6.5 12h11"/>',
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title.' · '.$gymName : config('app.name', 'Gimnasio') }}</title>

    {{-- Si en layouts/app.blade.php agregaste fuentes propias (por ejemplo la de font-heading), copiá esas líneas acá --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- Decide si el menú arranca abierto o cerrado ANTES de dibujar la página (evita el parpadeo).
         Va como archivo normal en public/js, no por Vite, porque tiene que ejecutarse de inmediato. --}}
    <script src="{{ asset('js/sidebar-init.js') }}"></script>

    @vite([
        'resources/css/app.css',
        'resources/css/sidebar.css',
        'resources/js/app.js',
        'resources/js/sidebar.js',
    ])
</head>
<body class="bg-stone-50 font-sans text-stone-900 antialiased">

    {{-- Fondo oscuro detrás del menú (solo en celular) --}}
    <div id="sidebar-backdrop" class="fixed inset-0 z-30 bg-stone-950/50" aria-hidden="true"></div>

    {{-- MENÚ LATERAL --}}
    <aside
        id="sidebar"
        class="fixed inset-y-0 left-0 z-40 flex w-72 max-w-[85vw] flex-col overflow-y-auto bg-stone-950 px-4 py-6 text-stone-200 lg:w-64"
        aria-label="Menú lateral"
        inert
    >
        <div class="flex items-center justify-between gap-3 px-1">
            <div class="flex min-w-0 items-center gap-3">
                <div class="flex h-10 w-10 flex-none items-center justify-center rounded-lg bg-lime-400 text-stone-950" aria-hidden="true">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        {!! $icons['dumbbell'] !!}
                    </svg>
                </div>

                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-white">
                        {{ $gymName }}
                    </p>
                    <p class="truncate text-xs text-stone-400">
                        Gestión de socios y cuotas
                    </p>
                </div>
            </div>

            {{-- Cerrar (solo en celular) --}}
            <button
                id="sidebar-close"
                type="button"
                class="inline-flex h-11 w-11 flex-none items-center justify-center rounded-lg text-stone-300 transition hover:bg-white/10 hover:text-white lg:hidden"
                aria-label="Cerrar el menú"
            >
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                    <path d="M6 6l12 12M18 6L6 18"/>
                </svg>
            </button>
        </div>

        <nav class="mt-8 flex-1" aria-label="Menú principal">
            @foreach (['main' => null, 'modules' => 'Módulos'] as $group => $groupLabel)
                @php $items = $modules->where('group', $group); @endphp

                @if ($items->isNotEmpty())
                    <div class="{{ $groupLabel ? 'mt-8' : '' }}">
                        @if ($groupLabel)
                            <p class="px-3 text-xs font-semibold uppercase tracking-[0.12em] text-stone-400">
                                {{ $groupLabel }}
                            </p>
                        @endif

                        <ul class="{{ $groupLabel ? 'mt-2' : '' }} space-y-1">
                            @foreach ($items as $item)
                                @continue(($item['owner_only'] ?? false) && ! $isOwner)

                                @php
                                    $href = null;

                                    if (! empty($item['route']) && \Illuminate\Support\Facades\Route::has($item['route'])) {
                                        $href = route($item['route']);
                                    } elseif (! empty($item['anchor'])) {
                                        $href = route('dashboard') . $item['anchor'];
                                    }

                                    // "members.index" también marca activo en members.show, members.edit, etc.
                                    $routeBase = ! empty($item['route']) ? \Illuminate\Support\Str::before($item['route'], '.') : null;
                                    $active = $routeBase && request()->routeIs($routeBase . '.*', $item['route']);
                                @endphp

                                <li>
                                    @if ($href)
                                        <a
                                            href="{{ $href }}"
                                            @if ($active) aria-current="page" @endif
                                            class="flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-medium transition {{ $active ? 'bg-white/10 text-white' : 'text-stone-300 hover:bg-white/5 hover:text-white' }}"
                                        >
                                            <svg viewBox="0 0 24 24" class="h-5 w-5 flex-none" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                {!! $icons[$item['icon']] ?? '' !!}
                                            </svg>
                                            <span class="flex-1">{{ $item['label'] }}</span>
                                        </a>
                                    @else
                                        <div class="flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-medium text-stone-400">
                                            <svg viewBox="0 0 24 24" class="h-5 w-5 flex-none" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                {!! $icons[$item['icon']] ?? '' !!}
                                            </svg>
                                            <span class="flex-1">{{ $item['label'] }}</span>
                                            <span class="rounded-full bg-stone-800 px-2 py-0.5 text-xs font-medium text-stone-300">
                                                Pronto
                                            </span>
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach
        </nav>

        {{-- USUARIO --}}
        <div class="mt-8 rounded-xl bg-white/5 p-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 flex-none items-center justify-center rounded-full bg-stone-200 text-sm font-semibold text-stone-950" aria-hidden="true">
                    {{ $initials }}
                </div>

                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-white">
                        {{ $user?->name }}
                    </p>
                    <p class="truncate text-xs text-stone-400">
                        {{ $roleLabel }}
                    </p>
                </div>
            </div>

            <div class="mt-3 flex flex-col gap-1">
                <a
                    href="{{ route('profile.edit') }}"
                    class="flex min-h-11 items-center rounded-lg px-3 text-sm font-medium text-stone-300 transition hover:bg-white/10 hover:text-white"
                >
                    Mi perfil
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button
                        type="submit"
                        class="flex min-h-11 w-full items-center rounded-lg px-3 text-left text-sm font-medium text-stone-300 transition hover:bg-white/10 hover:text-white"
                    >
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- PÁGINA --}}
    <div id="page">
        <header class="sticky top-0 z-20 flex h-14 items-center gap-3 border-b border-stone-200 bg-white/90 px-4 backdrop-blur">
            <button
                id="sidebar-toggle"
                type="button"
                class="inline-flex h-11 w-11 items-center justify-center rounded-lg text-stone-700 transition hover:bg-stone-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-lime-400"
                aria-label="Mostrar u ocultar el menú"
                aria-controls="sidebar"
                aria-expanded="false"
            >
                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
            </button>

            <span class="truncate text-sm font-semibold text-stone-900">
                {{ $gymName }}
            </span>
        </header>

        <main>
            {{-- Mensaje de confirmación (por ejemplo "Socio registrado") --}}
            @if (session('status'))
                <div class="px-6 pt-6 lg:px-10 xl:px-14">
                    <div class="mx-auto max-w-7xl">
                        <p role="status" class="rounded-lg border border-lime-300 bg-lime-50 px-4 py-3 text-sm font-medium text-lime-900">
                            {{ session('status') }}
                        </p>
                    </div>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    {{-- Scripts propios de cada pantalla (se agregan con @push('scripts')) --}}
    @stack('scripts')
</body>
</html>
