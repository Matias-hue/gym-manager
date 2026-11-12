<div {{ $attributes->class(['flex h-40 items-center justify-center rounded-lg bg-gray-50 px-4 text-center text-sm text-gray-400']) }}>
    {{ $slot->isEmpty() ? 'Sin datos en este período' : $slot }}
</div>
