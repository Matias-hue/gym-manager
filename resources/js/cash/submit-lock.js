// Evita que un doble clic (o un Enter repetido) envíe el formulario dos veces.
// Se activa en cualquier formulario con el atributo data-lock-submit.
// Si el botón tiene data-loading-text, cambia su texto mientras se envía.

const FORM_SELECTOR = 'form[data-lock-submit]';

function lock(form) {
    form.dataset.submitting = 'true';

    form.querySelectorAll('button[type="submit"]').forEach((button) => {
        button.disabled = true;

        if (button.dataset.loadingText) {
            button.dataset.originalText = button.textContent;
            button.textContent = button.dataset.loadingText;
        }
    });
}

function unlock(form) {
    delete form.dataset.submitting;

    form.querySelectorAll('button[type="submit"]').forEach((button) => {
        button.disabled = false;

        if (button.dataset.originalText) {
            button.textContent = button.dataset.originalText;
        }
    });
}

export function initSubmitLock() {
    document.querySelectorAll(FORM_SELECTOR).forEach((form) => {
        form.addEventListener('submit', (event) => {
            // Si otro módulo frenó el envío (por ejemplo, para pedir confirmación), no se bloquea nada
            if (event.defaultPrevented) {
                return;
            }

            if (form.dataset.submitting === 'true') {
                event.preventDefault();
                return;
            }

            form.dataset.submitting = 'true';

            // Se deshabilita después del evento para no interferir con el envío en sí
            setTimeout(() => lock(form), 0);
        });
    });

    // Al volver con el botón "atrás", el navegador puede traer la página con los botones bloqueados
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            document.querySelectorAll(FORM_SELECTOR).forEach(unlock);
        }
    });
}
