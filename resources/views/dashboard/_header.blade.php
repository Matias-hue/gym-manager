{{-- ENCABEZADO: fecha, buscador y acciones principales --}}
<header class="flex flex-col gap-6 border-b border-stone-200 pb-7 lg:flex-row lg:items-end lg:justify-between">
    <div>
        <p class="text-sm font-medium text-stone-500">
            {{ ucfirst(now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY')) }}
        </p>

        <h1 class="mt-2 font-heading text-3xl font-semibold tracking-tight text-stone-950 sm:text-4xl">
            Inicio
        </h1>

        <p class="mt-2 text-sm text-stone-500">
            Así está tu gimnasio hoy
        </p>
    </div>

    <div class="flex w-full flex-col gap-3 sm:flex-row lg:w-auto lg:items-center">
        <label class="relative flex min-w-0 flex-1 items-center lg:w-72">

            <span class="sr-only">
                Buscar socio por nombre o DNI
            </span>

            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400"
                aria-hidden="true"
            >
                <circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="1.7" />
                <path d="m16 16 4.2 4.2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
            </svg>

            <input
                type="search"
                class="ironwell-member-search w-full rounded-lg border border-stone-200 bg-white py-3 pl-10 pr-4 text-sm text-stone-900 outline-none transition placeholder:text-stone-400 focus:border-stone-400 focus:ring-2 focus:ring-lime-200"
                placeholder="Buscar socio por nombre o DNI"
                autocomplete="off"
                data-search-bound="1"
            />
        </label>

        <div class="flex flex-col gap-2 sm:flex-row">
            <a
                href="#dues"
                class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-950 bg-stone-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-stone-800 active:scale-[0.98]"
            >
                Cobrar
            </a>

            <a
                href="#attendance"
                class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm font-semibold text-stone-950 transition hover:bg-stone-50 active:scale-[0.98]"
            >
                Registrar visita
            </a>
        </div>
    </div>
</header>
