// Formulario de cierre de caja:
// 1) muestra la diferencia en vivo mientras se escribe el efectivo contado
// 2) pide confirmación en una ventana antes de cerrar (una caja cerrada no se reabre)

import { askConfirmation } from './confirm-modal';
import { formatMoney } from './money';

const TONES = {
    empty: 'bg-gray-50 text-gray-500',
    exact: 'bg-green-50 text-green-700',
    short: 'bg-red-50 text-red-700',
    over: 'bg-amber-50 text-amber-700',
};

const ALL_TONE_CLASSES = Object.values(TONES).join(' ').split(' ');

function confirmationMessage(expected, counted) {
    const difference = counted - expected;
    const lines = [`Esperado: ${formatMoney(expected)}`, `Contado: ${formatMoney(counted)}`];

    if (difference === 0) {
        lines.push('La caja cierra justa.');
    } else if (difference < 0) {
        lines.push(`Faltante de ${formatMoney(-difference)}.`);
    } else {
        lines.push(`Sobrante de ${formatMoney(difference)}.`);
    }

    lines.push('', 'Después de cerrar no se puede reabrir.');

    return lines.join('\n');
}

export function initCloseForm() {
    const form = document.querySelector('[data-cash-close]');

    if (!form) {
        return;
    }

    const expected = Number(form.dataset.expected);
    const input = form.querySelector('[data-counted-input]');
    const box = form.querySelector('[data-difference-box]');
    const label = form.querySelector('[data-difference-label]');
    const amount = form.querySelector('[data-difference-amount]');

    const render = () => {
        box.classList.remove(...ALL_TONE_CLASSES);

        if (input.value === '') {
            box.classList.add(...TONES.empty.split(' '));
            label.textContent = 'Diferencia';
            amount.textContent = 'Ingresá el efectivo contado';
            return;
        }

        const difference = Number(input.value) - expected;

        if (difference === 0) {
            box.classList.add(...TONES.exact.split(' '));
            label.textContent = 'La caja cierra justa';
            amount.textContent = formatMoney(0);
        } else if (difference < 0) {
            box.classList.add(...TONES.short.split(' '));
            label.textContent = 'Faltante';
            amount.textContent = `- ${formatMoney(-difference)}`;
        } else {
            box.classList.add(...TONES.over.split(' '));
            label.textContent = 'Sobrante';
            amount.textContent = `+ ${formatMoney(difference)}`;
        }
    };

    input.addEventListener('input', render);
    render();

    let confirmed = false;

    form.addEventListener('submit', async (event) => {
        if (confirmed) {
            return;
        }

        event.preventDefault();

        const accepted = await askConfirmation({
            title: '¿Cerrar la caja?',
            message: confirmationMessage(expected, Number(input.value)),
            acceptText: 'Sí, cerrar caja',
        });

        if (accepted) {
            confirmed = true;
            form.requestSubmit();
        }
    });
}
