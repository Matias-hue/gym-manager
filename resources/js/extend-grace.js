// Abre y cierra el modal de prórroga y le asigna la URL del socio elegido.
const modal = document.getElementById('extend-grace-modal');

if (modal) {
    const form = document.getElementById('extend-grace-form');
    const memberLabel = document.getElementById('extend-grace-member');
    const endsOnLabel = document.getElementById('extend-grace-ends-on');
    const dateInput = document.getElementById('grace_until');

    const open = ({ url, member, endsOn, minDate }) => {
        form.action = url;
        memberLabel.textContent = member;
        endsOnLabel.textContent = endsOn;
        dateInput.min = minDate;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');
        dateInput.focus();
    };

    const close = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');
    };

    document.querySelectorAll('[data-extend-grace]').forEach((button) => {
        button.addEventListener('click', () => {
            open({
                url: button.dataset.url,
                member: button.dataset.member,
                endsOn: button.dataset.endsOn,
                minDate: button.dataset.minDate,
            });
        });
    });

    modal.querySelectorAll('[data-extend-grace-close]').forEach((button) => {
        button.addEventListener('click', close);
    });

    // Cerrar con Escape o haciendo clic fuera de la caja
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') close();
    });
    modal.addEventListener('click', (event) => {
        if (event.target === modal) close();
    });

    // Tras un error de validación, reabrir el modal del último socio
    if (window.reopenExtendGrace) {
        const lastUrl = sessionStorage.getItem('extendGraceLast');
        if (lastUrl) {
            const button = document.querySelector(`[data-extend-grace][data-url="${lastUrl}"]`);
            if (button) button.click();
        }
    }

    form.addEventListener('submit', () => {
        sessionStorage.setItem('extendGraceLast', form.action);
    });
}