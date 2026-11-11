@props(['title' => null])

@php
    $appName = config('app.name', 'Gimnasio');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title.' · '.$appName : $appName }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/auth.js',
    ])
</head>
<body class="bg-stone-50 font-sans text-stone-900 antialiased">
    <div class="grid min-h-screen lg:grid-cols-2">

        {{-- PANEL DE MARCA (solo en pantallas grandes) --}}
        <aside class="relative hidden flex-col justify-between bg-stone-950 p-12 text-stone-200 lg:flex">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-lime-400 text-stone-950" aria-hidden="true">
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6.5 6.5v11M17.5 6.5v11M3 9.5v5M21 9.5v5M6.5 12h11"/>
                    </svg>
                </div>
                <span class="text-lg font-semibold text-white">{{ $appName }}</span>
            </div>

            <div>
                <h2 class="max-w-md text-4xl font-semibold leading-tight text-white">
                    Todo en orden, todos los días.
                </h2>
                <p class="mt-4 max-w-md text-base text-stone-400">
                    Tu gimnasio funcionando como vos querés.
                </p>
            </div>

            <p class="text-xs text-stone-500">&copy; {{ date('Y') }} {{ $appName }}</p>
        </aside>

        {{-- FORMULARIO --}}
        <main class="flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-sm">

                {{-- Logo (solo en celular, donde no se ve el panel) --}}
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-lime-400 text-stone-950" aria-hidden="true">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6.5 6.5v11M17.5 6.5v11M3 9.5v5M21 9.5v5M6.5 12h11"/>
                        </svg>
                    </div>
                    <span class="text-base font-semibold text-stone-900">{{ $appName }}</span>
                </div>

                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>