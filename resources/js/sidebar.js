// Comportamiento del menú lateral y del botón hamburguesa.
(function () {
    var root = document.documentElement;
    var sidebar = document.getElementById('sidebar');
    var toggle = document.getElementById('sidebar-toggle');
    var closeButton = document.getElementById('sidebar-close');
    var backdrop = document.getElementById('sidebar-backdrop');
    var desktop = window.matchMedia('(min-width: 1024px)');

    // Si la página no tiene menú (por ejemplo el login), no hay nada que hacer
    if (!sidebar || !toggle) return;

    function isOpen() {
        return root.dataset.sidebar === 'open';
    }

    // Sincroniza el botón y bloquea el foco dentro del menú cuando está cerrado
    function render() {
        toggle.setAttribute('aria-expanded', String(isOpen()));
        sidebar.inert = !isOpen();
    }

    function setOpen(open, remember) {
        root.dataset.sidebar = open ? 'open' : 'closed';

        // En computadora se recuerda la última elección
        if (remember && desktop.matches) {
            try {
                localStorage.setItem('sidebar', open ? 'open' : 'closed');
            } catch (e) {}
        }

        render();
    }

    toggle.addEventListener('click', function () {
        var willOpen = !isOpen();
        setOpen(willOpen, true);

        // En celular, al abrir el menú el foco pasa al botón de cerrar
        if (willOpen && !desktop.matches && closeButton) {
            closeButton.focus();
        }
    });

    if (closeButton) {
        closeButton.addEventListener('click', function () {
            setOpen(false, false);
            toggle.focus();
        });
    }

    if (backdrop) {
        backdrop.addEventListener('click', function () {
            setOpen(false, false);
        });
    }

    // Escape cierra el menú en celular
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && isOpen() && !desktop.matches) {
            setOpen(false, false);
            toggle.focus();
        }
    });

    // En celular, tocar un enlace del menú lo cierra
    sidebar.addEventListener('click', function (event) {
        if (!desktop.matches && event.target.closest('a')) {
            setOpen(false, false);
        }
    });

    // Si se cambia el tamaño de la ventana (por ejemplo, al girar el celular)
    desktop.addEventListener('change', function (event) {
        var open = event.matches;

        if (event.matches) {
            try {
                open = localStorage.getItem('sidebar') !== 'closed';
            } catch (e) {}
        }

        setOpen(open, false);
    });

    render();
})();
