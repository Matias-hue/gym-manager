// Se carga en el <head>, antes de dibujar la página, para que el menú no parpadee.
// Decide si arranca abierto (computadora, salvo que lo hayas cerrado) o cerrado (celular).
(function () {
    var open = window.matchMedia('(min-width: 1024px)').matches;

    try {
        if (open && localStorage.getItem('sidebar') === 'closed') open = false;
    } catch (e) {}

    document.documentElement.dataset.sidebar = open ? 'open' : 'closed';
})();
