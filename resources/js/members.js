// Pantalla de Socios: búsqueda en vivo.
// Cuando dejás de escribir, el formulario se envía solo y la lista se filtra.
(function () {
    var DELAY_MS = 400;

    document.querySelectorAll('[data-live-search]').forEach(function (input) {
        var form = input.closest('form');
        if (!form) return;

        var timer = null;
        var lastValue = input.value;

        input.addEventListener('input', function () {
            clearTimeout(timer);

            timer = setTimeout(function () {
                if (input.value.trim() === lastValue.trim()) return;
                lastValue = input.value;
                form.requestSubmit();
            }, DELAY_MS);
        });

        // Al volver de la búsqueda, el cursor queda al final del texto para seguir escribiendo
        if (input.value !== '') {
            input.focus();
            input.setSelectionRange(input.value.length, input.value.length);
        }
    });
})();
