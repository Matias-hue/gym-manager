@props(['url' => null, 'label' => 'Avisar'])

@if ($url)
    <a
        href="{{ $url }}"
        target="_blank"
        rel="noopener noreferrer"
        class="no-print inline-flex items-center rounded-md bg-emerald-600 px-2.5 py-1 text-xs font-medium text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
    >{{ $label }}</a>
@else
    <span class="text-xs text-gray-400">Sin teléfono</span>
@endif
