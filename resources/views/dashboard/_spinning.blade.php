{{-- CUPO DE SPINNING: bicicletas ocupadas por turno. Recibe $spinning (un ítem por plan con cupo). --}}
<section
    id="spinning"
    class="w-full rounded-xl border border-stone-200 bg-white p-5 sm:p-6"
    aria-labelledby="spinning-title"
>
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-stone-500">
            Clases
        </p>

        <h2
            id="spinning-title"
            class="mt-1 font-heading text-xl font-semibold tracking-tight"
        >
            Cupo de spinning
        </h2>
    </div>

    @forelse ($spinning as $item)
        <div class="mt-7 border-t border-stone-100 pt-6">
            <div class="flex items-center justify-between gap-3 text-sm">
                <span class="font-medium">
                    {{ $item['plan']->name }}
                </span>

                <span class="text-stone-500">
                    {{ $item['capacity'] }} bicicletas por turno
                </span>
            </div>

            <p class="mt-1 text-xs text-stone-500">
                {{ $item['days'] }}
                @if ($item['next_class'])
                    · próxima clase: {{ $item['next_class'] }}
                @endif
            </p>

            @foreach ($item['slots'] as $slot)
                <div class="mt-4">
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <span>
                            {{ $slot['label'] }}
                        </span>

                        <span class="flex items-center gap-2 text-stone-500">
                            {{ $slot['occupied'] }} / {{ $slot['capacity'] }}
                            @if ($slot['full'])
                                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-900">
                                    Completo
                                </span>
                            @endif
                        </span>
                    </div>

                    <div
                        class="mt-2 h-2 overflow-hidden rounded-full bg-stone-100"
                        role="progressbar"
                        aria-label="Bicicletas ocupadas en el {{ strtolower($slot['label']) }}"
                        aria-valuenow="{{ $slot['occupied'] }}"
                        aria-valuemin="0"
                        aria-valuemax="{{ $slot['capacity'] }}"
                    >
                        <div
                            class="h-full rounded-full {{ $slot['full'] ? 'bg-amber-500' : 'bg-lime-500' }}"
                            style="width: {{ $slot['percent'] }}%"
                        ></div>
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <div class="mt-7 border-t border-stone-100 pt-6">
            <p class="text-sm text-stone-500">
                Todavía no hay un plan con cupo por turno (spinning).
                @can('owner')
                    Podés crearlo en
                    <a href="{{ route('plans.index') }}" class="font-medium text-stone-950 underline underline-offset-2">Planes</a>.
                @endcan
            </p>
        </div>
    @endforelse
</section>