@props(['result'])

@php
    // Mismos colores que los mensajes del sistema: verde (ok), ámbar (aviso), rojo (cuota), piedra (no encontrado)
    $styles = [
        'ok'        => 'border-emerald-300 bg-emerald-50 text-emerald-900',
        'warning'   => 'border-amber-300 bg-amber-50 text-amber-900',
        'overdue'   => 'border-red-300 bg-red-50 text-red-800',
        'not_found' => 'border-stone-300 bg-stone-100 text-stone-800',
    ];
    $class = $styles[$result['status']] ?? $styles['not_found'];
@endphp

<div
    id="attendance-result"
    data-status="{{ $result['status'] }}"
    role="status"
    class="attendance-result rounded-xl border p-5 {{ $class }}"
>
    <p class="text-sm font-semibold uppercase tracking-[0.12em]">{{ $result['title'] }}</p>

    @if ($result['member'])
        <p class="font-heading mt-1 text-2xl font-semibold">{{ $result['member'] }}</p>
    @endif

    @if (! empty($result['messages']))
        <ul class="mt-3 list-disc space-y-1 pl-5 text-sm">
            @foreach ($result['messages'] as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    @endif

    @if ($result['member_id'])
        <div class="mt-4 flex flex-wrap gap-x-5 text-sm font-semibold">
            <a href="{{ route('members.show', $result['member_id']) }}" class="inline-flex min-h-11 items-center underline">
                Ver ficha
            </a>

            @if ($result['status'] === 'overdue')
                <a href="{{ route('payments.create', $result['member_id']) }}" class="inline-flex min-h-11 items-center underline">
                    Cobrar cuota
                </a>
            @endif
        </div>
    @endif
</div>
