<?php

namespace App\Providers;

use App\Models\Asistencias\Asistencia;
use App\Models\Empleados\Empleado;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Conteos usados por el menu lateral y por el tablero (una sola consulta por render)
        View::composer('partials.sidebar', function ($view) {
            $view->with('pendientes', [
                'vacaciones' => \App\Models\Asistencias\SolicitudVacaciones::where('estado', 'Pendiente')->count(),
                'justificaciones' => \App\Models\Asistencias\Justificacion::where('estado', 'Pendiente')->count(),
            ]);
        });

        // Hooks de modelo (equivalente a los doc_events de Frappe, pero en Eloquent)
        Empleado::saving(function (Empleado $empleado) {
            $empleado->nombre_completo = trim(collect([
                $empleado->primer_nombre,
                $empleado->segundo_nombre,
                $empleado->apellido,
            ])->filter()->implode(' '));

            if (blank($empleado->estado)) {
                $empleado->estado = 'Activo';
            }
        });

        Asistencia::saving(function (Asistencia $asistencia) {
            app(\App\Services\AsistenciaService::class)->calcular($asistencia);
        });
    }
}
