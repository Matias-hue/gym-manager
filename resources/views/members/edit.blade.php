<x-gym-layout title="Editar socio">
    <section
        class="min-h-[calc(100vh-3.5rem)] w-full px-6 py-9 lg:px-10 lg:py-10 xl:px-14"
        aria-labelledby="edit-title"
    >
        <div class="mx-auto max-w-2xl">

            <a href="{{ route('members.show', $member) }}" class="inline-flex min-h-11 items-center text-sm font-medium text-stone-500 transition hover:text-stone-900">
                ← Volver a la ficha
            </a>

            <header class="mt-2 border-b border-stone-200 pb-6">
                <h1 id="edit-title" class="font-heading text-3xl font-semibold tracking-tight text-stone-950">
                    Editar a {{ $member->full_name }}
                </h1>
            </header>

            <div class="mt-6 rounded-xl border border-stone-200 bg-white p-5 sm:p-6">
                @include('members._form', [
                    'member' => $member,
                    'action' => route('members.update', $member),
                    'method' => 'PUT',
                    'submitLabel' => 'Guardar cambios',
                ])
            </div>

        </div>
    </section>
</x-gym-layout>
