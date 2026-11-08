// Punto de entrada de la caja del día. Se carga desde resources/js/app.js con: import './cash';

import { initCloseForm } from './cash/close-form';
import { initSubmitLock } from './cash/submit-lock';
import { initToasts } from './cash/toast';

// El orden importa: el cierre de caja tiene que registrar su "submit" antes que el bloqueo,
// así el bloqueo ve que el envío fue frenado para pedir confirmación y no desactiva el botón.
initCloseForm();
initSubmitLock();
initToasts();
