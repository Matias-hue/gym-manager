document.querySelectorAll('[data-toggle-password]').forEach((button) => {
    const input = document.querySelector(button.dataset.togglePassword);

    if (!input) return;

    button.addEventListener('click', () => {
        const show = input.type === 'password';

        input.type = show ? 'text' : 'password';
        button.textContent = show ? 'Ocultar' : 'Mostrar';
        button.setAttribute('aria-pressed', String(show));
    });
});