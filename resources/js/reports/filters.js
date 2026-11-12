/*
 * Formulario de filtros:
 *  - "hasta" nunca puede quedar antes de "desde"
 *  - los selects con data-auto-submit aplican el filtro al cambiar
 */
function initFilters() {
    document.querySelectorAll('[data-report-filters]').forEach((form) => {
        const from = form.querySelector('[name="from"]');
        const to = form.querySelector('[name="to"]');

        if (from && to) {
            const sync = () => {
                to.min = from.value || '';

                if (from.value && to.value && to.value < from.value) {
                    to.value = from.value;
                }
            };

            from.addEventListener('change', sync);
            sync();
        }

        form.querySelectorAll('[data-auto-submit]').forEach((field) => {
            field.addEventListener('change', () => form.requestSubmit());
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFilters);
} else {
    initFilters();
}
