<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
        ],
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],
        // Expedientes y documentos de justificacion (nunca en el repositorio)
        'expedientes' => [
            'driver' => 'local',
            'root' => storage_path('app/expedientes'),
            'throw' => false,
        ],
    ],
];
