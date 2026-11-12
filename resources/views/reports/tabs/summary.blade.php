@php($charts = $data['charts'])

<x-reports.kpi-grid :kpis="$data['kpis']" />

<div class="grid gap-6 lg:grid-cols-3">
    <x-reports.chart-card
        class="lg:col-span-2"
        title="Ingresos"
        :subtitle="$data['granularity_label']"
        :chart="$charts['income']"
    />

    <x-reports.chart-card
        title="Estado de los socios"
        subtitle="Socios activos, hoy"
        :chart="$charts['states']"
    />
</div>

<x-reports.chart-card
    title="Asistencia"
    :subtitle="$data['granularity_label']"
    :chart="$charts['visits']"
    small
/>
