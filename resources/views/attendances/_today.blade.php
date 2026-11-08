{{-- Ingresos ya anotados hoy, con opción de quitar uno anotado por error --}}
<section class="rounded-xl border border-stone-200 bg-white p-5" aria-label="Ingresos de hoy">
    <div class="flex items-center justify-between gap-3">
        <h2 class="font-heading text-lg font-semibold text-stone-900">Ingresos de hoy</h2>

        <span class="rounded-full bg-lime-100 px-3 py-1 text-sm font-semibold text-lime-900">
            {{ $attendances->count() }}
        </span>
    </div>

    @forelse ($attendances as $attendance)
        @if ($loop->first)
            <ul class="mt-4 divide-y divide-stone-100">
        @endif

        <li class="flex items-center justify-between gap-3 py-1.5">
            <a
                href="{{ route('members.show', $attendance->member_id) }}"
                class="font-medium text-stone-900 hover:underline"
            >
                {{ $attendance->member->first_name }} {{ $attendance->member->last_name }}
            </a>

            <div class="flex items-center gap-3">
                <span class="text-sm text-stone-500">
                    {{ $attendance->checked_in_at->format('H:i') }} hs
                </span>

                <form
                    method="POST"
                    action="{{ route('attendances.destroy', $attendance) }}"
                    data-confirm="¿Quitar la visita de {{ $attendance->member->first_name }} {{ $attendance->member->last_name }}?"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="inline-flex min-h-11 items-center rounded-lg px-3 text-sm font-medium text-red-700 transition hover:bg-red-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-400"
                    >
                        Quitar
                    </button>
                </form>
            </div>
        </li>

        @if ($loop->last)
            </ul>
        @endif
    @empty
        <p class="mt-4 text-sm text-stone-500">Todavía no se anotó a nadie hoy.</p>
    @endforelse
</section>
