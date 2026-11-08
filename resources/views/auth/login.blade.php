<x-auth-layout title="Ingresar">
    <h1 class="text-2xl font-semibold text-stone-900">Ingresá a tu cuenta</h1>
    <p class="mt-1 text-sm text-stone-500">Usá el correo y la clave que te dio el dueño del gimnasio.</p>

    {{-- Mensaje de sesión (por ejemplo, "clave restablecida") --}}
    @if (session('status'))
        <p role="status" class="mt-6 rounded-lg border border-lime-300 bg-lime-50 px-4 py-3 text-sm font-medium text-lime-900">
            {{ session('status') }}
        </p>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        {{-- Correo --}}
        <div>
            <label for="email" class="block text-sm font-medium text-stone-700">Correo</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                class="mt-1.5 block min-h-11 w-full rounded-lg border-stone-300 bg-white px-3 text-sm text-stone-900 shadow-sm placeholder:text-stone-400 focus:border-lime-500 focus:ring-lime-400 @error('email') border-red-400 @enderror"
            >
            @error('email')
                <p role="alert" class="mt-1.5 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>

        {{-- Clave --}}
        <div>
            <label for="password" class="block text-sm font-medium text-stone-700">Clave</label>
            <div class="relative mt-1.5">
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    class="block min-h-11 w-full rounded-lg border-stone-300 bg-white py-0 pl-3 pr-20 text-sm text-stone-900 shadow-sm focus:border-lime-500 focus:ring-lime-400 @error('password') border-red-400 @enderror"
                >
                <button
                    type="button"
                    data-toggle-password="#password"
                    aria-pressed="false"
                    class="absolute inset-y-0 right-0 px-3 text-xs font-medium text-stone-500 transition hover:text-stone-900 focus:outline-none focus-visible:text-stone-900"
                >
                    Mostrar
                </button>
            </div>
            @error('password')
                <p role="alert" class="mt-1.5 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>

        {{-- Recordarme / olvidé mi clave --}}
        <div class="flex items-center justify-between gap-4">
            <label for="remember_me" class="inline-flex min-h-11 items-center gap-2 text-sm text-stone-600">
                <input
                    id="remember_me"
                    name="remember"
                    type="checkbox"
                    class="h-4 w-4 rounded border-stone-300 text-lime-500 focus:ring-lime-400"
                >
                Mantener la sesión
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm font-medium text-stone-700 underline-offset-4 hover:text-stone-950 hover:underline">
                    Olvidé mi clave
                </a>
            @endif
        </div>

        <button
            type="submit"
            class="flex min-h-11 w-full items-center justify-center rounded-lg bg-lime-400 px-4 text-sm font-semibold text-stone-950 transition hover:bg-lime-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-stone-950 focus-visible:ring-offset-2"
        >
            Ingresar
        </button>
    </form>
</x-auth-layout>