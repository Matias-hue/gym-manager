<x-gym-layout title="Nuevo socio">
    <section
        class="min-h-[calc(100vh-3.5rem)] w-full px-6 py-9 lg:px-10 lg:py-10 xl:px-14"
        aria-labelledby="create-title"
    >
        <div class="mx-auto max-w-2xl">

            <a href="{{ route('members.index') }}" class="inline-flex min-h-11 items-center text-sm font-medium text-stone-500 transition hover:text-stone-900">
                ← Volver a socios
            </a>

            <header class="mt-2 border-b border-stone-200 pb-6">
                <h1 id="create-title" class="font-heading text-3xl font-semibold tracking-tight text-stone-950">
                    Nuevo socio
                </h1>
                <p class="mt-2 text-sm text-stone-500">
                    Con nombre y apellido alcanza. Después podés registrar su primer pago desde la ficha.
                </p>
            </header>

            <div class="mt-6 rounded-xl border border-stone-200 bg-white p-5 sm:p-6">
                @include('members._form', [
                    'member' => null,
                    'action' => route('members.store'),
                    'method' => 'POST',
                    'submitLabel' => 'Guardar socio',
                ])
            </div>

        </div>
    </section>
</x-gym-layout>
