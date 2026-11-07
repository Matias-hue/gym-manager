{{--
    Una fila de la tabla "Quién tiene que pagar" del inicio.
    Recibe: $row (member, membership, state, ends_on, grace_end, extended, days_overdue).
--}}
@php
    $member = $row['member'];
    $membership = $row['membership'];
    $isGrace = $row['state'] === 'grace';

    $document = ctype_digit((string) $member->document_number)
        ? number_format((int) $member->document_number, 0, ',', '.')
        : $member->document_number;

    $days = $row['days_overdue'];
    $daysText = $days === 1 ? '1 día' : "{$days} días";
@endphp

<tr class="ironwell-search-row" data-payment-status="{{ $isGrace ? 'grace' : 'expired' }}">
    <td class="py-3.5 pr-4 font-medium">
        {{ $member->first_name }} {{ $member->last_name }}
        <span class="block text-xs font-normal text-stone-400">
            DNI {{ $document }}
        </span>
    </td>

    <td class="py-3.5 pr-4 text-stone-600">
        {{ $membership->plan->name }}
        @if ($membership->time_slot)
            <span class="block text-xs text-stone-400">
                Turno {{ $membership->time_slot }}
            </span>
        @endif
    </td>

    <td class="py-3.5 pr-4 text-stone-600">
        Venció el {{ $row['ends_on']->locale('es')->isoFormat('D MMM') }}
        <span class="block text-xs text-stone-400">
            @if ($isGrace)
                {{ $row['extended'] ? 'Prórroga' : 'Tolerancia' }} hasta el {{ $row['grace_end']->locale('es')->isoFormat('D MMM') }}
            @else
                Hace {{ $daysText }}
            @endif
        </span>
    </td>

    <td class="py-3.5 pr-4">
        @if ($isGrace)
            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-900">
                En gracia
            </span>
        @else
            <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-900">
                Vencido
            </span>
        @endif
    </td>

    <td class="py-3.5">
        <div class="flex flex-wrap gap-2">
            @if (filled($member->phone))
                {{-- POST: registra el aviso y abre WhatsApp en otra pestaña --}}
                <form method="POST" action="{{ route('dues.notify', $member) }}" target="_blank" rel="noopener">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex min-h-11 items-center rounded-md border border-stone-300 bg-white px-3 py-2 text-xs font-semibold text-stone-950 transition hover:bg-stone-50"
                    >
                        Avisar
                    </button>
                </form>
            @else
                <span class="inline-flex min-h-11 items-center px-2 text-xs text-stone-400">Sin celular</span>
            @endif

            <x-extend-grace-button :membership="$membership" compact />

            <a
                href="{{ route('payments.create', $member) }}"
                class="inline-flex min-h-11 items-center rounded-md border border-stone-950 bg-stone-950 px-3 py-2 text-xs font-semibold text-white transition hover:bg-stone-800"
            >
                Cobrar
            </a>
        </div>
    </td>
</tr>
