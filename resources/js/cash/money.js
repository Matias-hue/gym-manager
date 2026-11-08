const formatter = new Intl.NumberFormat('es-AR');

// 190000 -> "$ 190.000"
export function formatMoney(amount) {
    return `$ ${formatter.format(amount)}`;
}
