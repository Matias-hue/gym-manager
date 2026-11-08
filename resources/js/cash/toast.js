// Avisos que aparecen arriba a la derecha y se van solos.
// Cada aviso es un elemento [data-toast] que renderiza el servidor (ver partials/flash.blade.php).

const DISMISS_AFTER_MS = 4000;
const FADE_MS = 300;

function dismiss(toast) {
    toast.classList.add('opacity-0', 'translate-x-4');
    setTimeout(() => toast.remove(), FADE_MS);
}

export function initToasts() {
    document.querySelectorAll('[data-toast]').forEach((toast) => {
        toast.querySelector('[data-toast-close]')?.addEventListener('click', () => dismiss(toast));
        setTimeout(() => dismiss(toast), DISMISS_AFTER_MS);
    });
}
