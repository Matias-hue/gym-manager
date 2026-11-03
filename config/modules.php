<?php

return [
    ['key' => 'dashboard',  'label' => 'Inicio',        'route' => 'dashboard',     'enabled' => true,  'owner_only' => false],
    ['key' => 'members',    'label' => 'Socios',        'route' => 'members.index', 'enabled' => true,  'owner_only' => false],
    ['key' => 'dues',       'label' => 'Vencimientos',  'route' => 'dues.index',    'enabled' => true,  'owner_only' => false],
    ['key' => 'cash',       'label' => 'Caja del día', 'route' => 'cash.index',    'enabled' => true,  'owner_only' => false],
    ['key' => 'reports',    'label' => 'Reportes',      'route' => 'reports.index', 'enabled' => true,  'owner_only' => true],
    ['key' => 'training',   'label' => 'Entrenamiento', 'route' => null,            'enabled' => false, 'owner_only' => false],
];