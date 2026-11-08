// Ventana de confirmación en el centro de la pantalla (reemplaza al confirm() del navegador).
// Usa el <dialog data-confirm-dialog> de resources/views/cash/partials/confirm-modal.blade.php.
// Devuelve una promesa: true si se aceptó, false si se canceló, se apretó Esc o se tocó afuera.

// Ignora toques en el fondo justo después de abrir, por si el usuario hizo doble clic
const BACKDROP_GRACE_MS = 400;

export function askConfirmation({ title, message, acceptText = 'Confirmar' }) {
    const dialog = document.querySelector('[data-confirm-dialog]');

    if (!dialog) {
        return Promise.resolve(window.confirm(message));
    }

    dialog.querySelector('[data-confirm-title]').textContent = title;
    dialog.querySelector('[data-confirm-message]').textContent = message;
    dialog.querySelector('[data-confirm-accept]').textContent = acceptText;
    dialog.returnValue = '';

    return new Promise((resolve) => {
        const openedAt = performance.now();

        const onBackdropClick = (event) => {
            if (event.target === dialog && performance.now() - openedAt > BACKDROP_GRACE_MS) {
                dialog.close('');
            }
        };

        dialog.addEventListener('click', onBackdropClick);
        dialog.addEventListener(
            'close',
            () => {
                dialog.removeEventListener('click', onBackdropClick);
                resolve(dialog.returnValue === 'accept');
            },
            { once: true },
        );

        dialog.showModal();
    });
}
