<?php

use App\Services\Reports\AttendanceReport;
use App\Services\Reports\CashReport;
use App\Services\Reports\IncomeReport;
use App\Services\Reports\MembersReport;

return [

    /*
    |--------------------------------------------------------------------------
    | Pestañas del módulo de reportes
    |--------------------------------------------------------------------------
    | 'export' es la clave de exportación CSV asociada a la pestaña (o null).
    */
    'tabs' => [
        'summary'    => ['label' => 'Resumen',    'export' => null],
        'income'     => ['label' => 'Ingresos',   'export' => 'payments'],
        'members'    => ['label' => 'Socios',     'export' => 'members'],
        'attendance' => ['label' => 'Asistencia', 'export' => 'attendance'],
        'cash'       => ['label' => 'Caja',       'export' => 'cash'],
    ],

    // Clave de exportación => clase que genera las filas del CSV.
    'exports' => [
        'payments'   => IncomeReport::class,
        'members'    => MembersReport::class,
        'attendance' => AttendanceReport::class,
        'cash'       => CashReport::class,
    ],

    'ranges' => [
        'today'      => 'Hoy',
        '7d'         => 'Últimos 7 días',
        '30d'        => 'Últimos 30 días',
        'this_month' => 'Este mes',
        'last_month' => 'Mes pasado',
        'this_year'  => 'Este año',
    ],

    /*
    |--------------------------------------------------------------------------
    | Etiquetas
    |--------------------------------------------------------------------------
    */
    'methods' => [
        'cash'     => 'Efectivo',
        'transfer' => 'Transferencia',
        'card'     => 'Tarjeta',
        'online'   => 'Online',
    ],

    'attendance_methods' => [
        'document' => 'DNI',
        'qr'       => 'QR',
        'manual'   => 'Manual',
    ],

    'states' => [
        'current' => 'Al día',
        'grace'   => 'En gracia',
        'expired' => 'Vencido',
        'none'    => 'Sin cuota',
    ],

    'slots' => [
        'tarde' => 'Tarde',
        'noche' => 'Noche',
    ],

    // Día ISO de la semana (1 = lunes ... 7 = domingo).
    'weekdays' => [
        1 => 'Lun',
        2 => 'Mar',
        3 => 'Mié',
        4 => 'Jue',
        5 => 'Vie',
        6 => 'Sáb',
        7 => 'Dom',
    ],

    /*
    |--------------------------------------------------------------------------
    | Parámetros
    |--------------------------------------------------------------------------
    */
    'expiring_days'          => 7,   // "vencen pronto" = dentro de N días
    'inactive_days'          => 14,  // socio al día sin venir hace N días
    'top_limit'              => 10,  // largo de los rankings
    'list_limit'             => 25,  // largo de las listas de contacto
    'per_page'               => 15,  // filas por página en las tablas
    'month_group_after_days' => 62,  // períodos más largos se agrupan por mes

    /*
    |--------------------------------------------------------------------------
    | Mensajes de WhatsApp (:name, :gym y :date se reemplazan)
    |--------------------------------------------------------------------------
    */
    'whatsapp' => [
        'expiring' => 'Hola :name, te avisamos desde :gym que tu cuota vence el :date. ¡Te esperamos!',
        'overdue'  => 'Hola :name, desde :gym te recordamos que tu cuota venció el :date. Cuando puedas, pasá a regularizarla. ¡Gracias!',
        'inactive' => 'Hola :name, hace un tiempo que no te vemos por :gym. ¿Todo bien? ¡Te esperamos para entrenar!',
    ],

];
