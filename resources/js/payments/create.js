// Formulario de cobro: sugiere el monto, muestra el turno y evita el doble envío.
// La vista le pasa la ocupación de los turnos en data-occupancy del <form>.

const form = document.getElementById('collect-form');

if (form) {
    const occupancy = JSON.parse(form.dataset.occupancy || '{}');

    const plan = document.getElementById('plan_id');
    const months = document.getElementById('months');
    const amount = document.getElementById('amount');
    const slotWrapper = document.getElementById('slot-wrapper');
    const slot = document.getElementById('time_slot');
    const slotInfo = document.getElementById('slot-info');
    const btn = document.getElementById('submit-btn');

    let amountTouched = amount.value !== '';
    amount.addEventListener('input', () => { amountTouched = true; });

    const selected = () => plan.options[plan.selectedIndex];

    // Propone monto = precio del plan × meses mientras no se haya editado a mano
    function suggestAmount() {
        const price = parseInt(selected().dataset.price || '0', 10);
        const m = Math.max(1, parseInt(months.value || '1', 10));
        if (price && !amountTouched) amount.value = price * m;
    }

    // Muestra el turno solo en planes con cupo y la ocupación de cada uno
    function updateSlot() {
        const capacity = parseInt(selected().dataset.capacity || '0', 10);
        slotWrapper.classList.toggle('hidden', !capacity);

        if (!capacity) {
            slot.value = '';
            slotInfo.textContent = '';
            return;
        }

        const occ = occupancy[plan.value] || {};
        slotInfo.textContent =
            `Ocupación — tarde: ${occ.tarde ?? 0}/${capacity} · noche: ${occ.noche ?? 0}/${capacity}`;
    }

    plan.addEventListener('change', () => {
        amountTouched = false;
        suggestAmount();
        updateSlot();
    });
    months.addEventListener('input', suggestAmount);

    // Evita el doble clic (doble cobro)
    form.addEventListener('submit', () => {
        btn.disabled = true;
        btn.textContent = 'Guardando…';
    });

    suggestAmount();
    updateSlot();
}
