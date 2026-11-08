// Pantalla de visitas: foco, sugerencias en vivo y confirmación al quitar.

document.addEventListener('DOMContentLoaded', () => {
    initConfirm();

    const input = document.getElementById('attendance-code');

    if (!input) {
        return;
    }

    initFocus(input);
    initFadeOut();
    initSuggestions(input);
});

// En computadora el campo queda siempre listo para el próximo socio.
// En celular o tablet no se fuerza el foco, para que no salte el teclado solo.
function initFocus(input) {
    if (!window.matchMedia('(pointer: fine)').matches) {
        return;
    }

    input.focus({ preventScroll: true });
    input.select();

    document.addEventListener('click', (event) => {
        const interactive = event.target.closest('a, button, input, select, textarea, label');
        const selecting = String(window.getSelection()).length > 0;

        if (!interactive && !selecting) {
            input.focus({ preventScroll: true });
        }
    });
}

// Un resultado sin avisos se oculta solo
function initFadeOut() {
    const result = document.getElementById('attendance-result');

    if (result && result.dataset.status === 'ok') {
        setTimeout(() => {
            result.style.transition = 'opacity 0.4s';
            result.style.opacity = '0';
        }, 6000);
    }
}

// Sugerencias mientras se escribe: tocar una anota la visita
function initSuggestions(input) {
    const box = document.getElementById('attendance-suggestions');
    const itemTemplate = document.getElementById('attendance-suggestion-template');
    const emptyTemplate = document.getElementById('attendance-empty-template');
    const pickForm = document.getElementById('attendance-pick-form');
    const searchUrl = input.dataset.searchUrl;

    if (!box || !itemTemplate || !emptyTemplate || !pickForm || !searchUrl) {
        return;
    }

    const pickInput = pickForm.querySelector('input[name="member_id"]');
    let timer = null;
    let controller = null;

    const hide = () => {
        box.classList.add('hidden');
        box.replaceChildren();
    };

    const render = (members) => {
        box.replaceChildren();

        if (members.length === 0) {
            box.append(emptyTemplate.content.firstElementChild.cloneNode(true));
        }

        members.forEach((member) => {
            const item = itemTemplate.content.firstElementChild.cloneNode(true);

            item.querySelector('[data-name]').textContent = member.name;
            item.querySelector('[data-document]').textContent = 'DNI ' + member.document;
            item.querySelector('[data-badge]').classList.toggle('hidden', !member.checked_in);

            item.addEventListener('click', () => {
                pickInput.value = member.id;
                pickForm.submit();
            });

            box.append(item);
        });

        box.classList.remove('hidden');
    };

    const search = async () => {
        const term = input.value.trim();

        if (term.length < 2) {
            hide();
            return;
        }

        controller?.abort();
        controller = new AbortController();

        try {
            const response = await fetch(`${searchUrl}?q=${encodeURIComponent(term)}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                signal: controller.signal,
            });

            if (!response.ok) {
                hide();
                return;
            }

            const { data } = await response.json();
            render(data);
        } catch (error) {
            if (error.name !== 'AbortError') {
                hide();
            }
        }
    };

    // Espera un instante después de la última tecla para no consultar de más
    input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(search, 250);
    });

    // Flechas: bajar a la lista, moverse y volver al campo
    input.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown') {
            const first = box.querySelector('button');
            if (first) {
                event.preventDefault();
                first.focus();
            }
        }
        if (event.key === 'Escape') {
            hide();
        }
    });

    box.addEventListener('keydown', (event) => {
        const buttons = [...box.querySelectorAll('button')];
        const index = buttons.indexOf(document.activeElement);

        if (event.key === 'ArrowDown' && index < buttons.length - 1) {
            event.preventDefault();
            buttons[index + 1].focus();
        }
        if (event.key === 'ArrowUp') {
            event.preventDefault();
            index > 0 ? buttons[index - 1].focus() : input.focus();
        }
        if (event.key === 'Escape') {
            hide();
            input.focus();
        }
    });

    // Un clic fuera de la lista la cierra
    document.addEventListener('click', (event) => {
        if (!box.contains(event.target) && event.target !== input) {
            hide();
        }
    });
}

// Los formularios con data-confirm piden confirmación antes de enviarse
function initConfirm() {
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });
}
