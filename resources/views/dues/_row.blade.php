{{--
    Una fila de la lista de vencimientos.
    Recibe: $row (member, membership, state, bucket, days, ends_on, last_sent).
--}}
@php
    $member = $row['member'];
    $days = $row['days'];

    $plural = fn (int $n) => $n === 1 ? '1 día' : "{$n} días";

    [$label, $badge] = match ($row['bucket']) {
        'al_dia' => ['Al día · vence el '.$row['ends_on']?->format('d/m/Y'), 'border-emerald-200 bg-emerald-50 text-emerald-900'],
        'por_vencer' => [
            $days === 0 ? 'Vence hoy' : 'Vence en '.$plural($days),
            'border-amber-200 bg-amber-50 text-amber-900',
        ],
        'gracia' => ['En gracia · venció hace '.$plural(abs((int) $days)), 'border-orange-200 bg-orange-50 text-orange-900'],
        'vencidos' => ['Vencido · hace '.$plural(abs((int) $days)), 'border-red-200 bg-red-50 text-red-900'],
        default => ['Sin pagos registrados', 'border-stone-200 bg-stone-50 text-stone-700'],
    };

    $canNotify = $row['membership'] && filled($member->phone);

    // La prórroga solo tiene sentido para quien ya venció (en gracia o vencido)
    $canExtend = $row['membership'] && in_array($row['bucket'], ['gracia', 'vencidos'], true);
@endphp

<div class="flex flex-col gap-3 border-b border-stone-200 px-5 py-4 last:border-b-0 sm:flex-row sm:items-center sm:justify-between">
    <div class="min-w-0">
        <a href="{{ route('members.show', $member) }}" class="block truncate text-sm font-semibold text-stone-950 hover:underline">
            {{ $member->first_name }} {{ $member->last_name }}
        </a>

        <p class="mt-0.5 text-xs text-stone-500">
            @if ($row['membership'])
                {{ $row['membership']->plan->name }} · vence el {{ $row['ends_on']->format('d/m/Y') }}
            @else
                Todavía no pagó ninguna cuota
            @endif
        </p>

        <div class="mt-2 flex flex-wrap items-center gap-2">
            <span class="inline-flex rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $badge }}">
                {{ $label }}
            </span>

            @if ($row['last_sent'])
                <span class="text-xs text-stone-400">
                    Último aviso: {{ $row['last_sent']->format('d/m') }}
                </span>
            @endif
        </div>
    </div>

    <div class="flex shrink-0 flex-wrap gap-2">
        @if ($canNotify)
            {{-- POST: registra el aviso y redirige a WhatsApp en otra pestaña --}}
            <form method="POST" action="{{ route('dues.notify', $member) }}" target="_blank" rel="noopener">
                @csrf
                <button
                    type="submit"
                    class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm font-semibold text-stone-950 transition hover:bg-stone-50"
                >
                    Avisar
                </button>
            </form>
        @elseif ($row['membership'])
            <span class="inline-flex min-h-11 items-center px-2 text-xs text-stone-400">Sin celular</span>
        @endif

        @if ($canExtend)
            <x-extend-grace-button :membership="$row['membership']" />
        @endif

        <a
            href="{{ route('payments.create', $member) }}"
            class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-950 bg-stone-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-stone-800 active:scale-[0.98]"
        >
            Cobrar
        </a>
    </div>
</div>