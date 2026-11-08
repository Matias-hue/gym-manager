{{-- Socios con plan limitado (visitas por semana o turno) que pueden venir hoy y todavía no fueron anotados --}}
<section class="rounded-xl border border-stone-200 bg-white p-5" aria-label="Socios esperados hoy">
    <div class="flex items-center justify-between gap-3">
        <h2 class="font-heading text-lg font-semibold text-stone-900">Esperados hoy</h2>

        <span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-900">
            {{ $expected->flatten(1)->count() }}
        </span>
    </div>

    <p class="mt-1 text-xs text-stone-500">
        Planes con visitas limitadas o turno, según los días permitidos de cada plan.
    </p>

    @forelse ($expected as $label => $items)
        <div class="mt-5">
            <h3 class="text-xs font-semibold uppercase tracking-[0.12em] text-stone-500">
                {{ $label }} ({{ $items->count() }})
            </h3>

            <ul class="mt-2 divide-y divide-stone-100">
                @foreach ($items as $item)
                    <li class="flex items-center justify-between gap-3 py-1.5">
                        <span class="font-medium text-stone-900">{{ $item['name'] }}</span>

                        <form method="POST" action="{{ route('attendances.store') }}">
                            @csrf
                            <input type="hidden" name="member_id" value="{{ $item['member_id'] }}">

                            <button
                                type="submit"
                                data-loading-text="Anotando…"
                                class="inline-flex min-h-11 items-center rounded-lg border border-lime-600 bg-white px-4 text-sm font-semibold text-stone-950 transition hover:bg-lime-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-lime-400 focus-visible:ring-offset-2"
                            >
                                Anotar
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    @empty
        <p class="mt-4 text-sm text-stone-500">No quedan socios esperados por anotar.</p>
    @endforelse
</section>