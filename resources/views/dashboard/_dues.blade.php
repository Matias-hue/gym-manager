{{-- QUIÉN TIENE QUE PAGAR: reemplaza al cuaderno. Recibe $dues. --}}
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
                Todos · {{ $dues['all'] }}
            </button>

            <button
                type="button"
                class="ironwell-payment-filter rounded-md border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-600 transition hover:bg-stone-50"
                data-filter="grace"
                aria-pressed="false"
                data-filter-bounds="1"
            >
                En gracia · {{ $dues['grace'] }}
            </button>

            <button
                type="button"
                class="ironwell-payment-filter rounded-md border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-600 transition hover:bg-stone-50"
                data-filter="expired"
                aria-pressed="false"
                data-filter-bounds="1"
            >
                Vencidos · {{ $dues['expired'] }}
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
                    <th scope="col" class="pb-3 pr-4">Socio</th>
                    <th scope="col" class="pb-3 pr-4">Plan</th>
                    <th scope="col" class="pb-3 pr-4">Vencimiento</th>
                    <th scope="col" class="pb-3 pr-4">Estado</th>
                    <th scope="col" class="pb-3">Acciones</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-stone-100">
                @forelse ($dues['rows'] as $row)
                    @include('dashboard._due_row', ['row' => $row])
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-sm text-stone-500">
                            No hay nadie con la cuota en gracia ni vencida. 🎉
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>

        <p class="ironwell-empty-payments hidden py-5 text-sm text-stone-500">
            Ningún socio coincide con la búsqueda.
        </p>

    </div>

    <p class="mt-4 border-t border-stone-100 pt-4 text-xs text-stone-500">
        "Avisar" abre WhatsApp con el mensaje ya escrito. "Prórroga" extiende la tolerancia de un socio que avisó que paga más tarde.
        @if ($dues['truncated'])
            Se muestran los primeros {{ $dues['shown_per_state'] }} de cada estado.
        @endif
        <a href="{{ route('dues.index') }}" class="font-medium text-stone-950 underline underline-offset-2">
            Ver todos los vencimientos
        </a>
    </p>

</section>
