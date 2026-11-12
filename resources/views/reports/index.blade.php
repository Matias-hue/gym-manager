<x-gym-layout>
    @vite(['resources/css/reports.css', 'resources/js/reports.js'])

    <div class="reports mx-auto w-full max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        @include('reports.partials.header')
        @include('reports.partials.filters')
        @include('reports.partials.tabs')

        <div class="space-y-6">
            @include('reports.tabs.'.$tab)
        </div>
    </div>
</x-gym-layout>
