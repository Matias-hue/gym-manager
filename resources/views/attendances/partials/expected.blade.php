{{-- Socios con plan limitado (visitas por semana o turno) que pueden venir hoy y todavía no fueron anotados --}}
<section class="rounded-xl bg-white p-5 shadow">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Esperados hoy</h2>
        <span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-700">
            {{ $expected->flatten(1)->count() }}
        </span>
    </div>

    <p class="mt-1 text-xs text-gray-500">
        Planes con visitas limitadas o turno, según los días permitidos de cada plan.
    </p>

    @forelse ($expected as $label => $items)
        <div class="mt-4">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                {{ $label }} ({{ $items->count() }})
            </h3>

            <ul class="mt-2 divide-y divide-gray-100">
                @foreach ($items as $item)
                    <li class="flex items-center justify-between py-2">
                        <span class="font-medium text-gray-900">{{ $item['name'] }}</span>

                        <form method="POST" action="{{ route('attendances.store') }}">
                            @csrf
                            <input type="hidden" name="member_id" value="{{ $item['member_id'] }}">
                            <button type="submit"
                                    class="rounded-lg bg-indigo-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-indigo-700">
                                Anotar
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    @empty
        <p class="mt-4 text-sm text-gray-500">No quedan socios esperados por anotar.</p>
    @endforelse
</section>