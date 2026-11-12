@props([
    'title' => null,
    'subtitle' => null,
    'padding' => 'normal', // normal | none (para tablas a ancho completo)
])

<section {{ $attributes->class(['report-card rounded-xl border border-gray-200 bg-white shadow-sm']) }}>
    @if ($title)
        <header @class(['px-5 pt-5' => $padding === 'normal', 'px-5 py-4 border-b border-gray-100' => $padding === 'none'])>
            <h3 class="text-base font-semibold text-gray-900">{{ $title }}</h3>
            @if ($subtitle)
                <p class="mt-0.5 text-sm text-gray-500">{{ $subtitle }}</p>
            @endif
        </header>
    @endif

    <div @class(['p-5' => $padding === 'normal' && ! $title, 'px-5 pb-5 pt-4' => $padding === 'normal' && $title])>
        {{ $slot }}
    </div>
</section>
