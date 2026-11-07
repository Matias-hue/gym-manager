@props(['result'])

@php
    $styles = [
        'ok'        => 'border-green-300 bg-green-50 text-green-900',
        'warning'   => 'border-amber-300 bg-amber-50 text-amber-900',
        'overdue'   => 'border-red-300 bg-red-50 text-red-900',
        'not_found' => 'border-gray-300 bg-gray-50 text-gray-800',
    ];
    $class = $styles[$result['status']] ?? $styles['not_found'];
@endphp

<div id="attendance-result" data-status="{{ $result['status'] }}"
     class="attendance-result rounded-xl border-2 p-5 {{ $class }}" role="status">
    <p class="text-lg font-bold">{{ $result['title'] }}</p>

    @if ($result['member'])
        <p class="mt-1 text-2xl font-extrabold">{{ $result['member'] }}</p>
    @endif

    @if (! empty($result['messages']))
        <ul class="mt-3 list-disc space-y-1 pl-5 text-sm">
            @foreach ($result['messages'] as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    @endif

    @if ($result['member_id'])
        <div class="mt-4 flex flex-wrap gap-3 text-sm">
            <a href="{{ route('members.show', $result['member_id']) }}"
               class="font-semibold underline">Ver ficha</a>

            @if ($result['status'] === 'overdue')
                <a href="{{ route('payments.create', $result['member_id']) }}"
                   class="font-semibold underline">Cobrar cuota</a>
            @endif
        </div>
    @endif
</div>