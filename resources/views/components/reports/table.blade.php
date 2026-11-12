@props([
    // Encabezados en el formato 'Texto' => 'left' | 'right' | 'center'
    'headers' => [],
])

<div class="overflow-x-auto">
    <table {{ $attributes->class(['min-w-full divide-y divide-gray-200 text-sm']) }}>
        <thead class="bg-gray-50">
            <tr>
                @foreach ($headers as $label => $align)
                    <th scope="col" @class([
                        'whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500',
                        'text-left' => $align === 'left',
                        'text-right' => $align === 'right',
                        'text-center' => $align === 'center',
                    ])>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
            {{ $slot }}
        </tbody>
    </table>
</div>
