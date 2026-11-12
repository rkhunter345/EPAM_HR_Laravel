<?php

namespace App\Http\Controllers;

/**
 * Punto de entrada autenticado: deriva a cada rol a su propio tablero, tal como
 * se especifica en el documento (Empleado, Supervisor, RR. HH. y Administrador).
 */
class TableroController extends Controller
{
    public function __invoke()
    {
        $u = auth()->user();

        return match ($u?->rol) {
            'ADMIN' => redirect()->route('sistema.panel'),
            'RRHH' => redirect()->route('rrhh.panel'),
            'SUPERVISOR' => redirect()->route('equipo.panel'),
            default => redirect()->route('espacio.inicio'),
        };
    }
}
