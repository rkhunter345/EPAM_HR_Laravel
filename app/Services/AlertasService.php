<?php

namespace App\Services;

use App\Models\Asistencias\Asistencia;
use App\Models\Asistencias\Justificacion;
use App\Models\Asistencias\SalidaTemporal;
use App\Models\Asistencias\SolicitudPermiso;
use App\Models\Asistencias\SolicitudVacaciones;
use App\Models\Empleados\Empleado;
use App\Models\Nomina\DetallePlanilla;
use App\Models\Sistema\IotEvento;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Construye las alertas del sistema a partir de los eventos reales:
 * marcaciones, tardanzas, refrigerios, salidas temporales, IoT, solicitudes y boletas.
 * El documento exige que una alerta IoT nunca sea una sanción automática:
 * todas se presentan como "caso a revisar".
 */
class AlertasService
{
    public function paraEmpleado(Empleado $empleado, int $limite = 12): Collection
    {
        $alertas = collect();
        $hoy = Carbon::today();

        $asistencia = Asistencia::where('empleado_id', $empleado->id)->whereDate('fecha', $hoy)->first();
        if ($asistencia?->hora_entrada && ! $asistencia->hora_inicio_refrigerio) {
            $alertas->push([
                'titulo' => 'Recuerda registrar el inicio de tu refrigerio',
                'detalle' => 'Ingresaste a las '.substr($asistencia->hora_entrada, 0, 5).'. Marca el inicio del refrigerio cuando corresponda.',
                'hora' => substr($asistencia->hora_entrada, 0, 5), 'tono' => 'ambar', 'icono' => 'reloj',
                'estado' => 'Pendiente', 'tipo' => 'refrigerio',
            ]);
        }
        if ($asistencia?->hora_entrada && ! $asistencia->hora_salida) {
            $alertas->push([
                'titulo' => 'Tu jornada sigue abierta',
                'detalle' => 'No olvides marcar tu salida al terminar la jornada para cerrar el registro del día.',
                'hora' => 'Hoy', 'tono' => 'info', 'icono' => 'reloj', 'estado' => 'Informativa', 'tipo' => 'jornada',
            ]);
        }
        if ($asistencia && $asistencia->tardanza_min > 0) {
            $alertas->push([
                'titulo' => 'Tardanza registrada: '.$asistencia->tardanza_min.' min',
                'detalle' => 'Tu entrada se registró después de la tolerancia configurada. Puedes presentar una justificación.',
                'hora' => substr((string) $asistencia->hora_entrada, 0, 5), 'tono' => 'warn', 'icono' => 'ausencia',
                'estado' => 'Pendiente', 'tipo' => 'tardanza',
            ]);
        }

        $salida = SalidaTemporal::where('empleado_id', $empleado->id)
            ->where('estado', SalidaTemporal::ESTADO_ACTIVA)->latest('hora_salida')->first();
        if ($salida) {
            $alertas->push([
                'titulo' => 'Monitoreo pausado por salida temporal',
                'detalle' => 'Registra tu retorno al puesto para reanudar el monitoreo y evitar una alerta de ausencia.',
                'hora' => $salida->hora_salida?->format('H:i'), 'tono' => 'info', 'icono' => 'camara',
                'estado' => 'Monitoreo pausado', 'tipo' => 'salida',
            ]);
        }

        foreach (Justificacion::where('empleado_id', $empleado->id)->latest('id')->limit(2)->get() as $j) {
            $alertas->push([
                'titulo' => 'Justificación '.mb_strtolower($j->estado),
                'detalle' => $j->motivo.' · '.Carbon::parse($j->fecha)->format('d/m/Y'),
                'hora' => Carbon::parse($j->updated_at)->diffForHumans(), 'tono' => $j->estado === 'Aprobada' ? 'verde' : 'ambar',
                'icono' => 'solicitud', 'estado' => $j->estado, 'tipo' => 'justificacion',
            ]);
        }
        foreach (SolicitudVacaciones::where('empleado_id', $empleado->id)->latest('id')->limit(1)->get() as $v) {
            $alertas->push([
                'titulo' => 'Solicitud de '.mb_strtolower($v->tipo).' '.mb_strtolower($v->estado),
                'detalle' => Carbon::parse($v->desde)->format('d/m/Y').' al '.Carbon::parse($v->hasta)->format('d/m/Y'),
                'hora' => Carbon::parse($v->created_at)->diffForHumans(), 'tono' => $v->estado === 'Aprobada' ? 'verde' : 'ambar',
                'icono' => 'ausencia', 'estado' => $v->estado, 'tipo' => 'vacaciones',
            ]);
        }
        foreach (SalidaTemporal::where('empleado_id', $empleado->id)->whereNotNull('hora_retorno')->latest('id')->limit(1)->get() as $s) {
            $alertas->push([
                'titulo' => 'Se registró tu retorno al puesto',
                'detalle' => 'Salida '.$s->hora_salida?->format('H:i').' · retorno '.$s->hora_retorno?->format('H:i').' ('.$s->motivo.')',
                'hora' => $s->hora_retorno?->diffForHumans(), 'tono' => 'verde', 'icono' => 'check',
                'estado' => 'Completada', 'tipo' => 'retorno',
            ]);
        }

        $detalle = DetallePlanilla::with('periodo')->where('empleado_id', $empleado->id)->latest('id')->first();
        if ($detalle?->periodo) {
            $alertas->push([
                'titulo' => 'Tu boleta de pago está disponible',
                'detalle' => 'Periodo '.$detalle->periodo->periodo.'. Ya puedes consultarla y descargarla en PDF.',
                'hora' => Carbon::parse($detalle->created_at)->diffForHumans(), 'tono' => 'verde', 'icono' => 'planilla',
                'estado' => 'Disponible', 'tipo' => 'boleta',
            ]);
        }

        foreach (IotEvento::with('estacion')->where('empleado_id', $empleado->id)->latest('detectado_en')->limit(1)->get() as $ev) {
            $alertas->push([
                'titulo' => $ev->tipo === 'uso_celular' ? 'Uso de celular detectado en el puesto' : 'Ausencia del puesto detectada',
                'detalle' => 'Evento del '.$ev->detectado_en?->format('d/m/Y H:i').' · se encuentra en revisión, no es una sanción.',
                'hora' => $ev->detectado_en?->diffForHumans(), 'tono' => 'ambar', 'icono' => 'camara',
                'estado' => $ev->estado, 'tipo' => 'iot',
            ]);
        }

        return $alertas->take($limite)->values();
    }

    public function paraEquipo(Collection $empleados, int $limite = 12): Collection
    {
        $ids = $empleados->pluck('id');
        $alertas = collect();

        foreach (IotEvento::with(['empleado', 'estacion'])->whereIn('empleado_id', $ids)
            ->latest('detectado_en')->limit(6)->get() as $ev) {
            $alertas->push([
                'titulo' => ($ev->tipo === 'uso_celular' ? 'Uso de celular' : 'Ausencia del puesto').' · '.($ev->empleado->nombre_completo ?? '—'),
                'detalle' => ($ev->estacion->nombre ?? 'Estación').' · '.$ev->detectado_en?->format('H:i').' · en revisión',
                'hora' => $ev->detectado_en?->diffForHumans(), 'tono' => 'ambar', 'icono' => 'camara',
                'estado' => $ev->estado, 'tipo' => 'iot', 'empleado' => $ev->empleado,
            ]);
        }
        foreach (Asistencia::with('empleado')->whereIn('empleado_id', $ids)->whereDate('fecha', Carbon::today())
            ->where('estado', 'Tardanza')->limit(4)->get() as $a) {
            $alertas->push([
                'titulo' => 'Tardanza · '.($a->empleado->nombre_completo ?? '—'),
                'detalle' => 'Entrada '.substr((string) $a->hora_entrada, 0, 5).' · '.$a->tardanza_min.' min después de la tolerancia',
                'hora' => 'Hoy', 'tono' => 'warn', 'icono' => 'reloj', 'estado' => 'Tardanza', 'tipo' => 'tardanza',
                'empleado' => $a->empleado,
            ]);
        }
        foreach (Justificacion::with('empleado')->whereIn('empleado_id', $ids)->where('estado', 'Pendiente')->limit(4)->get() as $j) {
            $alertas->push([
                'titulo' => 'Justificación pendiente · '.($j->empleado->nombre_completo ?? '—'),
                'detalle' => $j->motivo.' · requiere decisión del supervisor',
                'hora' => Carbon::parse($j->created_at)->diffForHumans(), 'tono' => 'ambar', 'icono' => 'solicitud',
                'estado' => 'Pendiente', 'tipo' => 'justificacion', 'empleado' => $j->empleado,
            ]);
        }
        foreach (SolicitudPermiso::with('empleado')->whereIn('empleado_id', $ids)->where('estado', 'Pendiente')->limit(3)->get() as $p) {
            $alertas->push([
                'titulo' => 'Permiso pendiente · '.($p->empleado->nombre_completo ?? '—'),
                'detalle' => $p->motivo.' · '.Carbon::parse($p->fecha)->format('d/m/Y'),
                'hora' => Carbon::parse($p->created_at)->diffForHumans(), 'tono' => 'ambar', 'icono' => 'solicitud',
                'estado' => 'Pendiente', 'tipo' => 'permiso', 'empleado' => $p->empleado,
            ]);
        }

        return $alertas->take($limite)->values();
    }
}
