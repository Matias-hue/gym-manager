<dialog
    data-confirm-dialog
    class="m-auto w-[calc(100%-2rem)] max-w-md rounded-lg p-0 shadow-xl backdrop:bg-gray-900/50"
>
    <form method="dialog" class="p-6">
        <h2 class="text-lg font-semibold text-gray-900" data-confirm-title></h2>
        <p class="mt-2 whitespace-pre-line text-sm text-gray-600" data-confirm-message></p>

        <div class="mt-6 flex justify-end gap-3">
            <button
                type="submit"
                value="cancel"
                autofocus
                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                Cancelar
            </button>
            <button
                type="submit"
                value="accept"
                data-confirm-accept
                class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
            >
                Confirmar
            </button>
        </div>
    </form>
</dialog>
