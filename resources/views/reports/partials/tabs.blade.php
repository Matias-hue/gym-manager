<nav class="no-print border-b border-gray-200" aria-label="Secciones de reportes">
    <ul class="-mb-px flex gap-1 overflow-x-auto">
        @foreach ($tabs as $key => $item)
            <li>
                <a
                    href="{{ route('reports.index', ['tab' => $key] + $filters->periodQuery()) }}"
                    @class([
                        'inline-block whitespace-nowrap border-b-2 px-4 py-3 text-sm font-medium transition',
                        'border-indigo-600 text-indigo-600' => $tab === $key,
                        'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' => $tab !== $key,
                    ])
                    @if ($tab === $key) aria-current="page" @endif
                >{{ $item['label'] }}</a>
            </li>
        @endforeach
    </ul>
</nav>
