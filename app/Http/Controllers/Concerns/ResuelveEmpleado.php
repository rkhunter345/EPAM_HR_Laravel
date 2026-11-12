<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Empleados\Empleado;
use App\Models\Organizacion\Departamento;
use Illuminate\Support\Collection;

trait ResuelveEmpleado
{
    protected function empleadoActual(): ?Empleado
    {
        $u = auth()->user();

        return $u?->empleado_id ? Empleado::find($u->empleado_id) : null;
    }

    /**
     * Equipo del supervisor: únicamente los empleados activos del departamento
     * vinculado a su expediente por clave estable (empleados.departamento_id).
     *
     * Regla de fallo seguro (FASE 2.B del documento): si el supervisor no tiene
     * expediente o departamento, devuelve una colección VACÍA. Nunca devuelve
     * automáticamente todos los empleados activos. La comparación frágil entre
     * departamentos.responsable y el nombre visible del usuario se retiró.
     */
    protected function equipoDelSupervisor(): Collection
    {
        $empleado = $this->empleadoActual();

        if (! $empleado?->departamento_id) {
            return collect();
        }

        $departamento = Departamento::find($empleado->departamento_id);

        if (! $departamento) {
            return collect();
        }

        return Empleado::with(['departamento', 'designacion', 'calendario'])
            ->where('estado', 'Activo')
            ->where('departamento_id', $departamento->id)
            ->orderBy('nombre_completo')
            ->get();
    }

    /** IDs del equipo del supervisor (colección vacía si no tiene asignación). */
    protected function idsEquipoSupervisor(): Collection
    {
        return $this->equipoDelSupervisor()->pluck('id');
    }
}
