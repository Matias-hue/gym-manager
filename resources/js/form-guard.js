// Protección de formularios:
//  1. Después del primer envío se bloquean todos los botones hasta que la
//     página responda (así los clics repetidos no mandan nada de más).
//  2. Los formularios con data-confirm piden confirmación en un modal
//     (<x-confirm-modal />) en vez del alert del navegador.
//
// Atributos que lee de cada <form> / <button>:
//   data-confirm="¿Quitar la visita?"      -> activa el modal con ese mensaje
//   data-confirm-title="Quitar visita"     -> título del modal (opcional)
//   data-confirm-button="Quitar"           -> texto del botón de confirmar (opcional)
//   data-loading-text="Anotando…"          -> texto del botón mientras se envía (opcional)

const UNLOCK_AFTER_MS = 15000; // por si la conexión se cuelga y no hay respuesta

export function initFormGuard() {
    const modal = document.getElementById('confirm-modal');
    const titleEl = modal?.querySelector('[data-confirm-title]');
    const messageEl = modal?.querySelector('[data-confirm-message]');
    const acceptBtn = modal?.querySelector('[data-confirm-accept]');
    const cancelBtn = modal?.querySelector('[data-confirm-cancel]');

    let locked = false;
    let pendingForm = null;
    let unlockTimer = null;

    const submitButtons = () => document.querySelectorAll('button[type="submit"]');

    const lock = (form, submitter) => {
        locked = true;

        const main = submitter || form.querySelector('button[type="submit"]');

        // Se deshabilita en el siguiente ciclo para no interferir con el envío en curso
        setTimeout(() => {
            submitButtons().forEach((button) => {
                button.disabled = true;
                button.setAttribute('aria-disabled', 'true');
            });

            if (main) {
                main.dataset.originalHtml = main.innerHTML;
                main.textContent = main.dataset.loadingText || 'Enviando…';
                main.setAttribute('aria-busy', 'true');
            }

            form.querySelectorAll('input[type="text"]').forEach((field) => {
                field.readOnly = true;
            });
        }, 0);

        clearTimeout(unlockTimer);
        unlockTimer = setTimeout(unlock, UNLOCK_AFTER_MS);
    };

    const unlock = () => {
        locked = false;
        clearTimeout(unlockTimer);

        submitButtons().forEach((button) => {
            button.disabled = false;
            button.removeAttribute('aria-disabled');
            button.removeAttribute('aria-busy');

            if (button.dataset.originalHtml !== undefined) {
                button.innerHTML = button.dataset.originalHtml;
                delete button.dataset.originalHtml;
            }
        });

        document.querySelectorAll('input[type="text"][readonly]').forEach((field) => {
            field.readOnly = false;
        });
    };

    const ask = (form) => {
        pendingForm = form;

        titleEl.textContent = form.dataset.confirmTitle || 'Confirmar acción';
        messageEl.textContent = form.dataset.confirm;
        acceptBtn.textContent = form.dataset.confirmButton || 'Confirmar';

        modal.showModal();
    };

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        // Ya hay un envío en curso: se ignora este
        if (locked) {
            event.preventDefault();
            return;
        }

        // Pide confirmación antes de enviar
        if (form.dataset.confirm && modal && form.dataset.confirmed !== '1') {
            event.preventDefault();
            ask(form);
            return;
        }

        delete form.dataset.confirmed;
        lock(form, event.submitter);
    });

    if (modal) {
        acceptBtn.addEventListener('click', () => {
            const form = pendingForm;
            pendingForm = null;
            modal.close();

            if (form) {
                form.dataset.confirmed = '1';
                form.requestSubmit();
            }
        });

        cancelBtn.addEventListener('click', () => modal.close());

        // Clic en el fondo oscuro (fuera de la caja) cierra el modal
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                modal.close();
            }
        });

        modal.addEventListener('close', () => {
            pendingForm = null;
        });
    }

    // Si vuelve con el botón "atrás" del navegador, la página reaparece desbloqueada
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            unlock();
        }
    });
}

// Se activa solo al cargar el archivo: no hace falta llamarlo desde otro JS
document.addEventListener('DOMContentLoaded', initFormGuard);