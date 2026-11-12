// Cualquier elemento con data-print abre el diálogo de impresión / PDF.
document.addEventListener('click', (event) => {
    if (event.target.closest('[data-print]')) {
        window.print();
    }
});
