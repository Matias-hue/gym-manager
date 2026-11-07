{{-- Ingresos ya anotados hoy, con opción de quitar uno anotado por error --}}
<section class="rounded-xl bg-white p-5 shadow">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Ingresos de hoy</h2>
        <span class="rounded-full bg-indigo-100 px-3 py-1 text-sm font-semibold text-indigo-700">
            {{ $attendances->count() }}
        </span>
    </div>

    @forelse ($attendances as $attendance)
        @if ($loop->first)
            <ul class="mt-4 divide-y divide-gray-100">
        @endif

        <li class="flex items-center justify-between gap-3 py-2">
            <a href="{{ route('members.show', $attendance->member_id) }}"
               class="font-medium text-gray-900 hover:underline">
                {{ $attendance->member->first_name }} {{ $attendance->member->last_name }}
            </a>

            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-500">
                    {{ $attendance->checked_in_at->format('H:i') }} hs
                </span>

                <form method="POST" action="{{ route('attendances.destroy', $attendance) }}"
                      data-confirm="¿Quitar la visita de {{ $attendance->member->first_name }} {{ $attendance->member->last_name }}?">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm font-medium text-red-600 hover:underline">
                        Quitar
                    </button>
                </form>
            </div>
        </li>

        @if ($loop->last)
            </ul>
        @endif
    @empty
        <p class="mt-4 text-sm text-gray-500">Todavía no se anotó a nadie hoy.</p>
    @endforelse
</section>