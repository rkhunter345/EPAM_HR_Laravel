<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('epam:resumen', function () {
    $this->info('Empleados: '.\App\Models\Empleados\Empleado::count());
    $this->info('Marcaciones: '.\App\Models\Asistencias\Asistencia::count());
})->purpose('Muestra un resumen rápido del sistema EPAM HR');
