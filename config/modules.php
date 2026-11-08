<?php

// Menú lateral. Para activar una pantalla nueva, completá su 'route'.
// Si no hay 'route' ni 'anchor', el menú la muestra apagada con la etiqueta "Pronto".
return [
    ['group' => 'main', 'label' => 'Inicio', 'icon' => 'home',
        'route' => 'dashboard'],
    ['group' => 'main', 'label' => 'Socios', 'icon' => 'users',
        'route' => 'members.index'],
    ['group' => 'main', 'label' => 'Pagos', 'icon' => 'cash',
        'route' => null, 'anchor' => '#payments'],
    ['group' => 'main', 'label' => 'Visitas', 'icon' => 'check', 
        'route' => 'attendances.index'],
    ['group' => 'main', 'label' => 'Vencimientos', 'icon' => 'calendar',
        'route' => 'dues.index'],
    ['group' => 'main', 'label' => 'Caja del día', 'icon' => 'bank',
        'route' => 'cash.index'],
    ['group' => 'main', 'label' => 'Reportes', 'icon' => 'chart',
        'route' => null, 'owner_only' => true], // luego: 'reports.index'
    ['group' => 'modules', 'label' => 'Entrenamiento', 'icon' => 'dumbbell',
        'route' => null],
];
