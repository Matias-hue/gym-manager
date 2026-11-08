{{--
    Pantalla de visitas. Recibe del AttendanceController:
    $attendances, $expected, $result, $matches, $notice
--}}
<x-gym-layout title="Visitas">
    <section
        id="attendance"
        class="min-h-[calc(100vh-3.5rem)] w-full px-6 py-9 lg:px-10 lg:py-10 xl:px-14"
        aria-label="Registro de visitas"
    >
        <div class="mx-auto max-w-7xl">

            @include('attendances._header')

            @if ($notice)
                <div role="status" class="mt-4 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
                    {{ $notice }}
                </div>
            @endif

            <div class="mt-6 grid grid-cols-1 gap-6">

                @include('attendances._search')

                @if ($matches)
                    @include('attendances._matches', ['matches' => $matches])
                @endif

                @if ($result)
                    <x-attendance-result :result="$result" />
                @endif

                {{-- Dos cajas separadas: una al lado de la otra en pantallas grandes --}}
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                    @include('attendances._expected', ['expected' => $expected])

                    @include('attendances._today', ['attendances' => $attendances])
                </div>

            </div>

        </div>
    </section>

    @push('scripts')
        @vite(['resources/css/attendance.css', 'resources/js/attendance.js'])
    @endpush
</x-gym-layout>
