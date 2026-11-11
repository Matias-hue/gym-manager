<x-auth-layout title="Recuperar clave">
    <h1 class="text-2xl font-semibold text-stone-900">¿Olvidaste tu clave?</h1>
    <p class="mt-1 text-sm text-stone-500">
        Escribí tu correo y te enviamos un enlace para elegir una clave nueva.
    </p>

    @if (session('status'))
        <p role="status" class="mt-6 rounded-lg border border-lime-300 bg-lime-50 px-4 py-3 text-sm font-medium text-lime-900">
            {{ session('status') }}
        </p>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
        @csrf

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

        <button
            type="submit"
            class="flex min-h-11 w-full items-center justify-center rounded-lg bg-lime-400 px-4 text-sm font-semibold text-stone-950 transition hover:bg-lime-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-stone-950 focus-visible:ring-offset-2"
        >
            Enviar enlace
        </button>

        <a href="{{ route('login') }}" class="block text-center text-sm font-medium text-stone-700 underline-offset-4 hover:text-stone-950 hover:underline">
            Volver a ingresar
        </a>
    </form>
</x-auth-layout>