<?php

namespace App\Services;

use App\Models\Asistencias\Asistencia;
use App\Models\Empleados\Empleado;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Reglas de jornada peruana usadas por el módulo Asistencia.
 * Jornada de 8 horas = 480 minutos; el refrigerio no es tiempo efectivo.
 */
class AsistenciaService
{
    public const JORNADA_MIN = 480;

    /** Calcula tardanza, refrigerio y minutos trabajados de un registro. */
    public function calcular(Asistencia $a): Asistencia
    {
        $empleado = $a->empleado;
        $calendario = $empleado?->calendario;
        $entradaOficial = $calendario->hora_entrada ?? '08:00:00';
        $tolerancia = (int) ($calendario->tolerancia_min ?? 10);
        $refrigerioOficial = (int) ($calendario->refrigerio_min ?? 45);

        if ($a->hora_entrada) {
            $entrada = Carbon::parse($a->hora_entrada);
            $oficial = Carbon::parse($entradaOficial);
            $a->tardanza_min = max(0, $oficial->diffInMinutes($entrada, false) - $tolerancia);
        } else {
            $a->tardanza_min = 0;
        }

        if ($a->hora_inicio_refrigerio && $a->hora_fin_refrigerio) {
            $a->refrigerio_min = Carbon::parse($a->hora_inicio_refrigerio)
                ->diffInMinutes(Carbon::parse($a->hora_fin_refrigerio));
            $a->exceso_refrigerio_min = max(0, $a->refrigerio_min - $refrigerioOficial);
        }

        if ($a->hora_entrada && $a->hora_salida) {
            $bruto = Carbon::parse($a->hora_entrada)->diffInMinutes(Carbon::parse($a->hora_salida));
            $a->minutos_trabajados = max(0, $bruto - (int) $a->refrigerio_min);
        }

        if (($a->tardanza_min ?? 0) > 0 && $a->hora_entrada) {
            $a->estado = 'Tardanza';
        } elseif ($a->hora_entrada && ! $a->hora_salida) {
            $a->estado = 'Incompleto';
        } elseif (! $a->hora_entrada) {
            $a->estado = $a->estado ?: 'Ausente';
        } elseif ($a->estado !== 'Justificado') {
            $a->estado = 'Asistió';
        }

        return $a;
    }

    /** Resumen del período para el tablero: asistió / tardanza / ausente. */
    public function resumenPeriodo(?string $desde = null, ?string $hasta = null): array
    {
        $desde = $desde ?: now()->startOfMonth()->toDateString();
        $hasta = $hasta ?: now()->toDateString();

        $filas = Asistencia::query()->whereBetween('fecha', [$desde, $hasta])->get();

        return [
            'asistio' => $filas->where('estado', 'Asistió')->count(),
            'tardanza' => $filas->where('estado', 'Tardanza')->count(),
            'ausente' => $filas->where('estado', 'Ausente')->count(),
            'justificado' => $filas->where('estado', 'Justificado')->count(),
            'minutos' => (int) $filas->sum('minutos_trabajados'),
            'total' => $filas->count(),
        ];
    }

    /** Tasa de puntualidad en porcentaje (0-100). */
    public function puntualidad(array $resumen): float
    {
        $base = max(1, $resumen['total']);

        return round(($resumen['asistio'] / $base) * 100, 1);
    }

    /** Historial del empleado agrupado por día. */
    public function historial(Empleado $empleado, ?string $desde = null, ?string $hasta = null): Collection
    {
        return $empleado->asistencias()
            ->when($desde, fn ($q) => $q->whereDate('fecha', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('fecha', '<=', $hasta))
            ->orderByDesc('fecha')
            ->get();
    }
}
