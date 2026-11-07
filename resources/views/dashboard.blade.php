{{--
    Pantalla de inicio. Recibe del DashboardController (vía DashboardStats):
    $summary, $dues, $payments, $attendance, $spinning
--}}
<x-gym-layout>
    {{-- Modal de prórroga: lo usan los botones "Prórroga" de la tabla de vencimientos --}}
    <x-extend-grace-modal />

    <section
        id="home"
        class="min-h-[calc(100vh-3.5rem)] w-full px-6 py-9 lg:px-10 lg:py-10 xl:px-14"
        aria-label="Panel de gestión del gimnasio"
    >
        <div class="mx-auto max-w-7xl">

            @include('dashboard._header')

            @if (session('status'))
                <div role="status" class="mt-4 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div role="alert" class="mt-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @include('dashboard._summary', ['summary' => $summary])

            <div class="mt-6 grid grid-cols-1 gap-6">

                @include('dashboard._dues', ['dues' => $dues])

                @include('dashboard._payments', ['payments' => $payments])

                {{-- Dos cajas separadas: una al lado de la otra en pantallas grandes --}}
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                    @include('dashboard._attendance', ['attendance' => $attendance, 'summary' => $summary])

                    @include('dashboard._spinning', ['spinning' => $spinning])
                </div>

                @include('dashboard._modules')

            </div>

        </div>
    </section>
</x-gym-layout>