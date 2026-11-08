{{-- Varias coincidencias al enviar el formulario sin elegir una sugerencia --}}
<section class="rounded-xl border border-lime-300 bg-lime-50 p-5" aria-label="Socios que coinciden">
    <h2 class="font-heading text-base font-semibold text-stone-900">
        Hay varios socios que coinciden. ¿A quién anotamos?
    </h2>

    <ul class="mt-3 space-y-2">
        @foreach ($matches as $match)
            <li>
                <form method="POST" action="{{ route('attendances.store') }}">
                    @csrf
                    <input type="hidden" name="member_id" value="{{ $match['id'] }}">

                    <button
                        type="submit"
                        class="flex min-h-11 w-full items-center justify-between gap-3 rounded-lg border border-stone-200 bg-white px-4 py-2 text-left transition hover:bg-stone-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-lime-400"
                    >
                        <span class="font-medium text-stone-900">{{ $match['name'] }}</span>
                        <span class="text-sm text-stone-500">DNI {{ $match['document'] }}</span>
                    </button>
                </form>
            </li>
        @endforeach
    </ul>
</section>
