{{--
    Modal de confirmación reutilizable. Lo abre resources/js/form-guard.js
    cuando un <form> tiene data-confirm="mensaje".
--}}
<dialog
    id="confirm-modal"
    class="confirm-modal m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl border border-stone-200 bg-white p-0 text-stone-900 shadow-xl backdrop:bg-stone-950/50"
    aria-labelledby="confirm-modal-title"
    aria-describedby="confirm-modal-message"
>
    <div class="p-6">
        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-amber-100 text-amber-800" aria-hidden="true">
            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 8v5M12 16.5v.01"/>
                <circle cx="12" cy="12" r="9"/>
            </svg>
        </div>

        <h2 id="confirm-modal-title" data-confirm-title class="font-heading mt-4 text-lg font-semibold text-stone-900">
            Confirmar acción
        </h2>

        <p id="confirm-modal-message" data-confirm-message class="mt-2 text-sm text-stone-600"></p>
    </div>

    <div class="flex flex-col-reverse gap-3 rounded-b-2xl border-t border-stone-200 bg-stone-50 px-6 py-4 sm:flex-row sm:justify-end">
        <button
            type="button"
            data-confirm-cancel
            autofocus
            class="inline-flex min-h-11 items-center justify-center rounded-lg border border-lime-600 bg-white px-5 text-sm font-semibold text-stone-950 transition hover:bg-lime-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-lime-400 focus-visible:ring-offset-2"
        >
            Cancelar
        </button>

        <button
            type="button"
            data-confirm-accept
            class="inline-flex min-h-11 items-center justify-center rounded-lg bg-red-600 px-5 text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2"
        >
            Confirmar
        </button>
    </div>
</dialog>