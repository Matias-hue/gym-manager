// Alta masiva de socios: filas dinámicas, Enter para avanzar y turno según el plan.
// La vista deja el molde de fila en <template id="row-template"> y el próximo
// índice libre en data-next-index del <form>.

const form = document.getElementById('bulk-form');

if (form) {
    const table = document.getElementById('rows-table');
    const template = document.getElementById('row-template');
    const defaultPlan = document.getElementById('plan_id');
    const counter = document.getElementById('row-count');
    const addButton = document.getElementById('add-row');
    const submitButton = document.getElementById('submit-btn');

    let nextIndex = parseInt(form.dataset.nextIndex || '0', 10);

    const rows = () => [...table.querySelectorAll('tbody[data-row]')];

    // Campos que se recorren con Enter, en orden, saltando los deshabilitados
    const fields = (row) => [...row.querySelectorAll('input, select')].filter((el) => !el.disabled);

    function updateCounter() {
        counter.textContent = rows().length;
    }

    // El turno solo se habilita si el plan efectivo de la fila tiene cupo
    function syncSlot(row) {
        const rowPlan = row.querySelector('[data-plan]');
        const slot = row.querySelector('[data-slot]');
        const option = rowPlan.value ? rowPlan.selectedOptions[0] : defaultPlan.selectedOptions[0];
        const hasCapacity = parseInt(option?.dataset.capacity || '0', 10) > 0;

        slot.disabled = !hasCapacity;
        if (!hasCapacity) slot.value = '';
    }

    function addRow() {
        table.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', nextIndex++));
        const row = rows().at(-1);
        syncSlot(row);
        updateCounter();
        return row;
    }

    // Enter avanza al siguiente campo; en el último de la fila pasa a la fila de abajo
    form.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' || event.target.tagName !== 'INPUT') return;

        event.preventDefault();

        const row = event.target.closest('tbody[data-row]');
        if (!row) return;

        const list = fields(row);
        const position = list.indexOf(event.target);

        if (position < list.length - 1) {
            list[position + 1].focus();
            return;
        }

        const following = row.nextElementSibling ?? addRow();
        fields(following)[0]?.focus();
    });

    form.addEventListener('click', (event) => {
        const remove = event.target.closest('[data-remove]');
        if (!remove) return;

        remove.closest('tbody[data-row]').remove();
        if (rows().length === 0) addRow();
        updateCounter();
    });

    form.addEventListener('change', (event) => {
        if (event.target === defaultPlan) {
            rows().forEach(syncSlot);
        } else if (event.target.matches('[data-plan]')) {
            syncSlot(event.target.closest('tbody[data-row]'));
        }
    });

    addButton.addEventListener('click', () => {
        fields(addRow())[0]?.focus();
    });

    // Evita el doble clic (doble carga)
    form.addEventListener('submit', () => {
        submitButton.disabled = true;
        submitButton.textContent = 'Guardando…';
    });

    rows().forEach(syncSlot);
    updateCounter();
}
