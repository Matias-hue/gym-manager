<x-gym-layout>
    <section
        id="home"
        class="min-h-[calc(100vh-3.5rem)] w-full px-6 py-9 lg:px-10 lg:py-10 xl:px-14"
        aria-label="Panel de gestión del gimnasio"
    >
        <div class="mx-auto max-w-7xl">

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
                            class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400 pointer-events-none"
                            aria-hidden="true"
                        >
                            <circle
                                cx="10.8"
                                cy="10.8"
                                r="6.8"
                                stroke="currentColor"
                                stroke-width="1.7"
                            />

                            <path
                                d="m16 16 4.2 4.2"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                            />
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

            {{-- RESUMEN (datos de ejemplo, luego vienen del controlador) --}}
            <div class="mt-7 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                <article class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm shadow-stone-900/[0.02]">
                    <p class="text-sm text-stone-500">
                        Socios activos
                    </p>

                    <p class="mt-3 font-heading text-3xl font-semibold tracking-tight">
                        86
                    </p>

                    <p class="mt-2 text-xs text-stone-500">
                        82 al día y 4 en gracia
                    </p>
                </article>

                <article class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm shadow-stone-900/[0.02]">
                    <p class="text-sm text-stone-500">
                        Cobrado hoy
                    </p>

                    <p class="mt-3 font-heading text-3xl font-semibold tracking-tight">
                        $ 67.000
                    </p>

                    <p class="mt-2 text-xs text-stone-500">
                        3 cobros registrados
                    </p>
                </article>

                <article class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm shadow-stone-900/[0.02]">
                    <p class="text-sm text-stone-500">
                        Visitas de hoy
                    </p>

                    <p class="mt-3 font-heading text-3xl font-semibold tracking-tight">
                        3
                    </p>

                    <p class="mt-2 text-xs text-stone-500">
                        Solo socios de musculación 3 días y spinning
                    </p>
                </article>

                <article class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm shadow-stone-900/[0.02]">
                    <p class="text-sm text-stone-500">
                        Cuotas por cobrar
                    </p>

                    <p class="mt-3 font-heading text-3xl font-semibold tracking-tight">
                        6
                    </p>

                    <p class="mt-2 text-xs text-stone-500">
                        4 en gracia y 2 vencidas
                    </p>
                </article>

            </div>

            <div class="mt-6 grid grid-cols-1 gap-6">

                {{-- QUIÉN TIENE QUE PAGAR: reemplaza al cuaderno --}}
                <section
                    id="dues"
                    class="w-full rounded-xl border border-stone-200 bg-white p-5 sm:p-6"
                    aria-labelledby="dues-title"
                >

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-stone-500">
                                Recepción
                            </p>

                            <h2
                                id="dues-title"
                                class="mt-1 font-heading text-xl font-semibold tracking-tight"
                            >
                                Quién tiene que pagar
                            </h2>
                        </div>

                        <div
                            class="flex flex-wrap gap-2"
                            role="group"
                            aria-label="Filtrar cuotas por estado"
                        >

                            <button
                                type="button"
                                class="ironwell-payment-filter rounded-md bg-stone-950 px-3 py-2 text-xs font-semibold text-white"
                                data-filter="all"
                                aria-pressed="true"
                                data-filter-bounds="1"
                            >
                                Todos · 6
                            </button>

                            <button
                                type="button"
                                class="ironwell-payment-filter rounded-md border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-600 transition hover:bg-stone-50"
                                data-filter="grace"
                                aria-pressed="false"
                                data-filter-bounds="1"
                            >
                                En gracia · 4
                            </button>

                            <button
                                type="button"
                                class="ironwell-payment-filter rounded-md border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-600 transition hover:bg-stone-50"
                                data-filter="expired"
                                aria-pressed="false"
                                data-filter-bounds="1"
                            >
                                Vencidos · 2
                            </button>

                        </div>

                    </div>

                    <div class="mt-5 overflow-x-auto">

                        <table class="w-full min-w-[760px] text-left text-sm">

                            <caption class="sr-only">
                                Socios con la cuota en gracia o vencida
                            </caption>

                            <thead class="border-b border-stone-200 text-xs font-medium text-stone-500">
                                <tr>
                                    <th scope="col" class="pb-3 pr-4">
                                        Socio
                                    </th>
                                    <th scope="col" class="pb-3 pr-4">
                                        Plan
                                    </th>
                                    <th scope="col" class="pb-3 pr-4">
                                        Vencimiento
                                    </th>
                                    <th scope="col" class="pb-3 pr-4">
                                        Estado
                                    </th>
                                    <th scope="col" class="pb-3">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>

                            {{-- En cada fila: cambiar href="#" de "Avisar" por $member->whatsappUrl('...') --}}
                            <tbody class="divide-y divide-stone-100">

                                <tr class="ironwell-search-row" data-payment-status="grace">
                                    <td class="py-3.5 pr-4 font-medium">
                                        Rocío Giménez
                                        <span class="block text-xs font-normal text-stone-400">
                                            DNI 39.654.321
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-4 text-stone-600">
                                        Musculación libre
                                    </td>
                                    <td class="py-3.5 pr-4 text-stone-600">
                                        Venció el 27 oct
                                        <span class="block text-xs text-stone-400">
                                            Tolerancia hasta el 10 nov
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-4">
                                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-900">
                                            En gracia
                                        </span>
                                    </td>
                                    <td class="py-3.5">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="#" class="inline-flex min-h-11 items-center rounded-md border border-stone-300 bg-white px-3 py-2 text-xs font-semibold text-stone-950 transition hover:bg-stone-50">
                                                Avisar
                                            </a>
                                            <button type="button" class="inline-flex min-h-11 items-center rounded-md border border-stone-300 bg-white px-3 py-2 text-xs font-semibold text-stone-950 transition hover:bg-stone-50">
                                                Prórroga
                                            </button>
                                            <a href="#" class="inline-flex min-h-11 items-center rounded-md border border-stone-950 bg-stone-950 px-3 py-2 text-xs font-semibold text-white transition hover:bg-stone-800">
                                                Cobrar
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="ironwell-search-row" data-payment-status="grace">
                                    <td class="py-3.5 pr-4 font-medium">
                                        Gonzalo Peralta
                                        <span class="block text-xs font-normal text-stone-400">
                                            DNI 37.812.345
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-4 text-stone-600">
                                        Musculación 3 días
                                    </td>
                                    <td class="py-3.5 pr-4 text-stone-600">
                                        Venció el 24 oct
                                        <span class="block text-xs text-stone-400">
                                            Tolerancia hasta el 7 nov
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-4">
                                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-900">
                                            En gracia
                                        </span>
                                    </td>
                                    <td class="py-3.5">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="#" class="inline-flex min-h-11 items-center rounded-md border border-stone-300 bg-white px-3 py-2 text-xs font-semibold text-stone-950 transition hover:bg-stone-50">
                                                Avisar
                                            </a>
                                            <button type="button" class="inline-flex min-h-11 items-center rounded-md border border-stone-300 bg-white px-3 py-2 text-xs font-semibold text-stone-950 transition hover:bg-stone-50">
                                                Prórroga
                                            </button>
                                            <a href="#" class="inline-flex min-h-11 items-center rounded-md border border-stone-950 bg-stone-950 px-3 py-2 text-xs font-semibold text-white transition hover:bg-stone-800">
                                                Cobrar
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="ironwell-search-row" data-payment-status="grace">
                                    <td class="py-3.5 pr-4 font-medium">
                                        Sofía Medina
                                        <span class="block text-xs font-normal text-stone-400">
                                            DNI 41.275.690
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-4 text-stone-600">
                                        Spinning
                                        <span class="block text-xs text-stone-400">
                                            Turno tarde · lun, mié y vie
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-4 text-stone-600">
                                        Venció el 29 oct
                                        <span class="block text-xs text-stone-400">
                                            Tolerancia hasta el 12 nov
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-4">
                                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-900">
                                            En gracia
                                        </span>
                                    </td>
                                    <td class="py-3.5">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="#" class="inline-flex min-h-11 items-center rounded-md border border-stone-300 bg-white px-3 py-2 text-xs font-semibold text-stone-950 transition hover:bg-stone-50">
                                                Avisar
                                            </a>
                                            <button type="button" class="inline-flex min-h-11 items-center rounded-md border border-stone-300 bg-white px-3 py-2 text-xs font-semibold text-stone-950 transition hover:bg-stone-50">
                                                Prórroga
                                            </button>
                                            <a href="#" class="inline-flex min-h-11 items-center rounded-md border border-stone-950 bg-stone-950 px-3 py-2 text-xs font-semibold text-white transition hover:bg-stone-800">
                                                Cobrar
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="ironwell-search-row" data-payment-status="grace">
                                    <td class="py-3.5 pr-4 font-medium">
                                        Nicolás Franco
                                        <span class="block text-xs font-normal text-stone-400">
                                            DNI 35.118.402
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-4 text-stone-600">
                                        Musculación libre
                                    </td>
                                    <td class="py-3.5 pr-4 text-stone-600">
                                        Venció el 15 oct
                                        <span class="block text-xs text-stone-400">
                                            Prórroga hasta el 8 nov
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-4">
                                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-900">
                                            En gracia
                                        </span>
                                    </td>
                                    <td class="py-3.5">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="#" class="inline-flex min-h-11 items-center rounded-md border border-stone-300 bg-white px-3 py-2 text-xs font-semibold text-stone-950 transition hover:bg-stone-50">
                                                Avisar
                                            </a>
                                            <button type="button" class="inline-flex min-h-11 items-center rounded-md border border-stone-300 bg-white px-3 py-2 text-xs font-semibold text-stone-950 transition hover:bg-stone-50">
                                                Prórroga
                                            </button>
                                            <a href="#" class="inline-flex min-h-11 items-center rounded-md border border-stone-950 bg-stone-950 px-3 py-2 text-xs font-semibold text-white transition hover:bg-stone-800">
                                                Cobrar
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="ironwell-search-row" data-payment-status="expired">
                                    <td class="py-3.5 pr-4 font-medium">
                                        Valentina Ríos
                                        <span class="block text-xs font-normal text-stone-400">
                                            DNI 43.560.217
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-4 text-stone-600">
                                        Spinning
                                        <span class="block text-xs text-stone-400">
                                            Turno noche · lun, mié y vie
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-4 text-stone-600">
                                        Venció el 10 oct
                                        <span class="block text-xs text-stone-400">
                                            Hace 24 días
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-4">
                                        <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-900">
                                            Vencido
                                        </span>
                                    </td>
                                    <td class="py-3.5">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="#" class="inline-flex min-h-11 items-center rounded-md border border-stone-300 bg-white px-3 py-2 text-xs font-semibold text-stone-950 transition hover:bg-stone-50">
                                                Avisar
                                            </a>
                                            <button type="button" class="inline-flex min-h-11 items-center rounded-md border border-stone-300 bg-white px-3 py-2 text-xs font-semibold text-stone-950 transition hover:bg-stone-50">
                                                Prórroga
                                            </button>
                                            <a href="#" class="inline-flex min-h-11 items-center rounded-md border border-stone-950 bg-stone-950 px-3 py-2 text-xs font-semibold text-white transition hover:bg-stone-800">
                                                Cobrar
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="ironwell-search-row" data-payment-status="expired">
                                    <td class="py-3.5 pr-4 font-medium">
                                        Joaquín Villalba
                                        <span class="block text-xs font-normal text-stone-400">
                                            DNI 40.903.558
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-4 text-stone-600">
                                        Musculación 3 días
                                    </td>
                                    <td class="py-3.5 pr-4 text-stone-600">
                                        Venció el 2 oct
                                        <span class="block text-xs text-stone-400">
                                            Hace 32 días
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-4">
                                        <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-900">
                                            Vencido
                                        </span>
                                    </td>
                                    <td class="py-3.5">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="#" class="inline-flex min-h-11 items-center rounded-md border border-stone-300 bg-white px-3 py-2 text-xs font-semibold text-stone-950 transition hover:bg-stone-50">
                                                Avisar
                                            </a>
                                            <button type="button" class="inline-flex min-h-11 items-center rounded-md border border-stone-300 bg-white px-3 py-2 text-xs font-semibold text-stone-950 transition hover:bg-stone-50">
                                                Prórroga
                                            </button>
                                            <a href="#" class="inline-flex min-h-11 items-center rounded-md border border-stone-950 bg-stone-950 px-3 py-2 text-xs font-semibold text-white transition hover:bg-stone-800">
                                                Cobrar
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                        <p class="ironwell-empty-payments hidden py-5 text-sm text-stone-500">
                            Ningún socio coincide con la búsqueda.
                        </p>

                    </div>

                    <p class="mt-4 border-t border-stone-100 pt-4 text-xs text-stone-500">
                        "Avisar" abre WhatsApp con el mensaje ya escrito. "Prórroga" extiende la tolerancia de un socio que avisó que paga más tarde. Los datos son de ejemplo.
                    </p>

                </section>

                {{-- COBROS RECIENTES --}}
                <section
                    id="payments"
                    class="w-full rounded-xl border border-stone-200 bg-white p-5 sm:p-6"
                    aria-labelledby="payments-title"
                >

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-stone-500">
                            Caja
                        </p>

                        <h2
                            id="payments-title"
                            class="mt-1 font-heading text-xl font-semibold tracking-tight"
                        >
                            Cobros recientes
                        </h2>
                    </div>

                    <div class="mt-5 overflow-x-auto">

                        <table class="w-full min-w-[640px] text-left text-sm">

                            <caption class="sr-only">
                                Últimos cobros registrados
                            </caption>

                            <thead class="border-b border-stone-200 text-xs font-medium text-stone-500">
                                <tr>
                                    <th scope="col" class="pb-3 pr-4">
                                        Socio
                                    </th>
                                    <th scope="col" class="pb-3 pr-4">
                                        Plan
                                    </th>
                                    <th scope="col" class="pb-3 pr-4">
                                        Fecha
                                    </th>
                                    <th scope="col" class="pb-3 pr-4">
                                        Monto
                                    </th>
                                    <th scope="col" class="pb-3">
                                        Forma de pago
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-stone-100">

                                <tr>
                                    <td class="py-3.5 pr-4 font-medium">
                                        Martina Gómez
                                        <span class="block text-xs font-normal text-stone-400">
                                            DNI 40.123.456
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-4 text-stone-600">
                                        Musculación libre
                                    </td>
                                    <td class="py-3.5 pr-4 text-stone-600">
                                        3 nov
                                    </td>
                                    <td class="py-3.5 pr-4">
                                        $ 25.000
                                    </td>
                                    <td class="py-3.5 text-stone-600">
                                        Efectivo
                                    </td>
                                </tr>

                                <tr>
                                    <td class="py-3.5 pr-4 font-medium">
                                        Lucas Benítez
                                        <span class="block text-xs font-normal text-stone-400">
                                            DNI 38.456.789
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-4 text-stone-600">
                                        Musculación 3 días
                                    </td>
                                    <td class="py-3.5 pr-4 text-stone-600">
                                        3 nov
                                    </td>
                                    <td class="py-3.5 pr-4">
                                        $ 20.000
                                    </td>
                                    <td class="py-3.5 text-stone-600">
                                        Transferencia
                                    </td>
                                </tr>

                                {{-- Ejemplo: el precio de spinning todavía hay que confirmarlo con el dueño --}}
                                <tr>
                                    <td class="py-3.5 pr-4 font-medium">
                                        Camila Ortiz
                                        <span class="block text-xs font-normal text-stone-400">
                                            DNI 42.987.654
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-4 text-stone-600">
                                        Spinning
                                        <span class="block text-xs text-stone-400">
                                            Turno noche
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-4 text-stone-600">
                                        3 nov
                                    </td>
                                    <td class="py-3.5 pr-4">
                                        $ 22.000
                                    </td>
                                    <td class="py-3.5 text-stone-600">
                                        Mercado Pago
                                    </td>
                                </tr>

                                <tr>
                                    <td class="py-3.5 pr-4 font-medium">
                                        Federico Acosta
                                        <span class="block text-xs font-normal text-stone-400">
                                            DNI 36.222.111
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-4 text-stone-600">
                                        Musculación 3 días
                                        <span class="block text-xs text-stone-400">
                                            2 semanas, mitad del precio
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-4 text-stone-600">
                                        2 nov
                                    </td>
                                    <td class="py-3.5 pr-4">
                                        $ 10.000
                                    </td>
                                    <td class="py-3.5 text-stone-600">
                                        Efectivo
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                </section>

                {{-- VISITAS, CUPO DE SPINNING Y ESTADO DE CUOTAS --}}
                <section
                    id="attendance"
                    class="w-full rounded-xl border border-stone-200 bg-white p-5 sm:p-6"
                    aria-labelledby="attendance-title"
                >
                    <!-- Fila 1: encabezado -->
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-stone-500">
                                Hoy
                            </p>

                            <h2
                                id="attendance-title"
                                class="mt-1 font-heading text-xl font-semibold tracking-tight"
                            >
                                Pulso del gimnasio
                            </h2>
                        </div>

                        <span class="rounded-full bg-lime-100 px-2.5 py-1 text-xs font-medium text-lime-900">
                            En vivo
                        </span>
                    </div>

                    <!-- Fila 2: visitas de hoy (solo planes con límite) -->
                    <div class="mt-7 border-t border-stone-100 pt-6">
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="font-medium">
                                Visitas de hoy
                            </span>

                            <span class="text-stone-500">
                                3 ingresos
                            </span>
                        </div>

                        <ul class="mt-3 divide-y divide-stone-100">
                            <li class="flex items-center justify-between gap-4 py-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium">
                                        Mariana Sosa
                                    </p>
                                    <p class="text-xs text-stone-500">
                                        Musculación 3 días · 2 de 3 visitas esta semana
                                    </p>
                                </div>
                                <span class="text-xs font-medium text-stone-500">
                                    15:45
                                </span>
                            </li>

                            <li class="flex items-center justify-between gap-4 py-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium">
                                        Tomás Aguirre
                                    </p>
                                    <p class="text-xs text-stone-500">
                                        Musculación 3 días · 1 de 3 visitas esta semana
                                    </p>
                                </div>
                                <span class="text-xs font-medium text-stone-500">
                                    09:30
                                </span>
                            </li>

                            <li class="flex items-center justify-between gap-4 py-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium">
                                        Ezequiel Núñez
                                    </p>
                                    <p class="text-xs text-stone-500">
                                        Musculación 3 días · 2 de 3 visitas esta semana
                                    </p>
                                </div>
                                <span class="text-xs font-medium text-stone-500">
                                    08:10
                                </span>
                            </li>
                        </ul>

                        <p class="mt-2 text-xs text-stone-500">
                            Se avisa si un socio supera sus 3 visitas por semana, pero no se le bloquea el ingreso. Musculación libre no se registra.
                        </p>
                    </div>

                    <!-- Fila 3: cupo de spinning -->
                    <div class="mt-6 border-t border-stone-100 pt-6">
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="font-medium">
                                Cupo de spinning
                            </span>

                            <span class="text-stone-500">
                                20 bicicletas por turno
                            </span>
                        </div>

                        <p class="mt-1 text-xs text-stone-500">
                            Lunes, miércoles y viernes · próxima clase: mañana
                        </p>

                        <div class="mt-4">
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span>
                                    Turno tarde
                                </span>

                                <span class="text-stone-500">
                                    17 / 20
                                </span>
                            </div>

                            <div
                                class="mt-2 h-2 overflow-hidden rounded-full bg-stone-100"
                                role="progressbar"
                                aria-label="Bicicletas ocupadas en el turno tarde"
                                aria-valuenow="17"
                                aria-valuemin="0"
                                aria-valuemax="20"
                            >
                                <div class="h-full w-[85%] rounded-full bg-lime-500"></div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span>
                                    Turno noche
                                </span>

                                <span class="flex items-center gap-2 text-stone-500">
                                    20 / 20
                                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-900">
                                        Completo
                                    </span>
                                </span>
                            </div>

                            <div
                                class="mt-2 h-2 overflow-hidden rounded-full bg-stone-100"
                                role="progressbar"
                                aria-label="Bicicletas ocupadas en el turno noche"
                                aria-valuenow="20"
                                aria-valuemin="0"
                                aria-valuemax="20"
                            >
                                <div class="h-full w-full rounded-full bg-amber-500"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 4: socios al día -->
                    <div class="mt-6 border-t border-stone-100 pt-6">
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="font-medium">
                                Socios al día
                            </span>

                            <span class="text-stone-500">
                                82 / 86
                            </span>
                        </div>

                        <div
                            class="mt-3 h-2 overflow-hidden rounded-full bg-stone-100"
                            role="progressbar"
                            aria-label="Socios activos que están al día"
                            aria-valuenow="82"
                            aria-valuemin="0"
                            aria-valuemax="86"
                        >
                            <div class="h-full w-[95%] rounded-full bg-stone-800"></div>
                        </div>

                        <p class="mt-2 text-xs text-stone-500">
                            Los otros 4 socios activos están en gracia
                        </p>
                    </div>
                </section>

                {{-- ESPACIO PARA MÓDULOS FUTUROS --}}
                <section
                    class="w-full rounded-xl border border-dashed border-stone-300 p-5 sm:p-6"
                    aria-labelledby="modules-title"
                >
                    <h2
                        id="modules-title"
                        class="font-heading text-xl font-semibold tracking-tight"
                    >
                        Más módulos
                    </h2>

                    <p class="mt-1 text-sm text-stone-500">
                        Cada módulo nuevo se suma al menú y puede traer su propio bloque en esta pantalla. El sistema base ya funciona sin ninguno.
                    </p>

                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="rounded-lg border border-dashed border-stone-300 bg-white p-4">
                            <p class="text-sm font-semibold">
                                Entrenamiento y rutinas
                            </p>
                            <p class="mt-1 text-xs text-stone-500">
                                Alumnos con entrenador, rutina asignada y progreso.
                            </p>
                            <span class="mt-3 inline-block rounded-full bg-stone-100 px-2.5 py-1 text-xs font-medium text-stone-700">
                                Próximamente
                            </span>
                        </div>

                        <div class="rounded-lg border border-dashed border-stone-300 bg-white p-4">
                            <p class="text-sm font-semibold">
                                Reserva de clases
                            </p>
                            <p class="mt-1 text-xs text-stone-500">
                                Reservar bicicleta por clase en lugar de turno fijo.
                            </p>
                            <span class="mt-3 inline-block rounded-full bg-stone-100 px-2.5 py-1 text-xs font-medium text-stone-700">
                                Más adelante
                            </span>
                        </div>

                        <div class="rounded-lg border border-dashed border-stone-300 bg-white p-4">
                            <p class="text-sm font-semibold">
                                Seguimiento físico
                            </p>
                            <p class="mt-1 text-xs text-stone-500">
                                Peso, medidas y fotos de progreso de cada socio.
                            </p>
                            <span class="mt-3 inline-block rounded-full bg-stone-100 px-2.5 py-1 text-xs font-medium text-stone-700">
                                Más adelante
                            </span>
                        </div>
                    </div>
                </section>

            </div>

        </div>
    </section>
</x-gym-layout>