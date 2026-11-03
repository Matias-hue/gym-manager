<?php

/*
 * Ítems del menú lateral.
 *
 * - group:      'main' (menú principal) o 'modules' (módulos extra).
 * - route:      nombre de la ruta cuando la pantalla ya existe (ej. 'members.index').
 * - anchor:     mientras no exista la pantalla, lleva a una sección del dashboard (ej. '#dues').
 * - owner_only: true = solo lo ve el dueño.
 *
 * Si un ítem no tiene ni ruta ni anchor, el menú lo muestra apagado con la etiqueta "Pronto".
 * Para activar uno cuando termines su pantalla, solo completá 'route'. No hay que tocar nada más.
 */

return [

    ['group' => 'main', 'label' => 'Inicio',       'icon' => 'home',     'route' => 'dashboard'],
    ['group' => 'main', 'label' => 'Socios',       'icon' => 'users',    'route' => null],  // luego: 'members.index'
    ['group' => 'main', 'label' => 'Cobros',       'icon' => 'cash',     'route' => null, 'anchor' => '#payments'],
    ['group' => 'main', 'label' => 'Visitas',      'icon' => 'check',    'route' => null, 'anchor' => '#attendance'],
    ['group' => 'main', 'label' => 'Vencimientos', 'icon' => 'calendar', 'route' => null, 'anchor' => '#dues'],  // luego: 'dues.index'
    ['group' => 'main', 'label' => 'Caja del día', 'icon' => 'bank',     'route' => null],  // luego: 'cash.index'
    ['group' => 'main', 'label' => 'Reportes',     'icon' => 'chart',    'route' => null, 'owner_only' => true],  // luego: 'reports.index'

    ['group' => 'modules', 'label' => 'Entrenamiento', 'icon' => 'dumbbell', 'route' => null],

];