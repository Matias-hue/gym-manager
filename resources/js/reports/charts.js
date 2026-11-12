import Chart from 'chart.js/auto';

/*
 * Dibuja todos los <canvas data-chart="..."> de la página.
 * El JSON lo arma App\Services\Reports\ChartData (datos, sin colores).
 */

const PALETTE = [
    '#4f46e5', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444',
    '#8b5cf6', '#14b8a6', '#f97316', '#64748b', '#ec4899',
];

// Colores con significado, para estados y movimientos de caja.
const TONES = {
    success: '#10b981',
    warning: '#f59e0b',
    danger: '#ef4444',
    info: '#0ea5e9',
    neutral: '#94a3b8',
};

const nf = new Intl.NumberFormat('es-AR', { maximumFractionDigits: 1 });
const nfMoney = new Intl.NumberFormat('es-AR', { maximumFractionDigits: 0 });
const nfCompact = new Intl.NumberFormat('es-AR', { notation: 'compact', maximumFractionDigits: 1 });

const valueFormatters = {
    number: (v) => nf.format(v),
    money: (v) => `$ ${nfMoney.format(v)}`,
    percent: (v) => `${nf.format(v)} %`,
};

const tickFormatters = {
    number: (v) => nf.format(v),
    money: (v) => `$ ${nfCompact.format(v)}`,
    percent: (v) => `${nf.format(v)} %`,
};

Chart.defaults.font.family = 'inherit';
Chart.defaults.color = '#6b7280';

function toneColor(tone, fallbackIndex) {
    return TONES[tone] ?? PALETTE[fallbackIndex % PALETTE.length];
}

function withAlpha(hex, alpha) {
    const value = hex.replace('#', '');
    const r = parseInt(value.slice(0, 2), 16);
    const g = parseInt(value.slice(2, 4), 16);
    const b = parseInt(value.slice(4, 6), 16);

    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

function decorateDataset(spec, dataset, index) {
    const tones = spec.options?.tones ?? [];

    if (spec.type === 'doughnut') {
        return {
            ...dataset,
            backgroundColor: spec.labels.map((_, i) => toneColor(tones[i], i)),
            borderColor: '#ffffff',
            borderWidth: 2,
        };
    }

    const color = toneColor(tones[index], index);

    if (spec.type === 'line') {
        return {
            ...dataset,
            borderColor: color,
            backgroundColor: withAlpha(color, 0.12),
            borderWidth: 2,
            tension: 0.3,
            fill: spec.datasets.length === 1,
            pointRadius: spec.labels.length > 31 ? 0 : 3,
            pointHoverRadius: 5,
        };
    }

    return {
        ...dataset,
        backgroundColor: color,
        borderRadius: 4,
        maxBarThickness: 36,
    };
}

function buildConfig(spec) {
    const format = spec.format in valueFormatters ? spec.format : 'number';
    const horizontal = Boolean(spec.options?.horizontal);
    const isDoughnut = spec.type === 'doughnut';

    const options = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: {
                display: isDoughnut || spec.datasets.length > 1,
                position: 'bottom',
                labels: { usePointStyle: true, boxWidth: 8 },
            },
            tooltip: {
                callbacks: {
                    label: (ctx) => {
                        const raw = typeof ctx.parsed === 'number'
                            ? ctx.parsed
                            : (horizontal ? ctx.parsed.x : ctx.parsed.y);
                        const name = isDoughnut ? ctx.label : ctx.dataset.label;
                        const text = valueFormatters[format](raw);

                        return name ? `${name}: ${text}` : text;
                    },
                },
            },
        },
    };

    if (isDoughnut) {
        options.cutout = '62%';
        options.interaction = { mode: 'nearest', intersect: true };
    } else {
        const valueAxis = {
            beginAtZero: true,
            grid: { color: '#f3f4f6' },
            ticks: { callback: (v) => tickFormatters[format](v) },
        };
        const categoryAxis = {
            grid: { display: false },
            ticks: { maxRotation: 0, autoSkip: true },
        };

        if (horizontal) {
            options.indexAxis = 'y';
            options.scales = { x: valueAxis, y: categoryAxis };
        } else {
            options.scales = { x: categoryAxis, y: valueAxis };
        }
    }

    return {
        type: spec.type,
        data: {
            labels: spec.labels,
            datasets: spec.datasets.map((dataset, i) => decorateDataset(spec, dataset, i)),
        },
        options,
    };
}

function renderCharts() {
    const charts = [];

    document.querySelectorAll('canvas[data-chart]').forEach((canvas) => {
        try {
            const spec = JSON.parse(canvas.dataset.chart);
            charts.push(new Chart(canvas, buildConfig(spec)));
        } catch (error) {
            console.error('No se pudo dibujar el gráfico', error);
        }
    });

    // Al imprimir, los gráficos se redibujan al tamaño de la página.
    window.addEventListener('beforeprint', () => charts.forEach((chart) => chart.resize()));
    window.addEventListener('afterprint', () => charts.forEach((chart) => chart.resize()));
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', renderCharts);
} else {
    renderCharts();
}
