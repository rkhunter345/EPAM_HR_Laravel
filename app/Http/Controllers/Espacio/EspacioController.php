<?php

namespace App\Http\Controllers\Espacio;

use App\Http\Controllers\Concerns\ResuelveEmpleado;
use App\Http\Controllers\Controller;
use App\Models\Asistencias\Asistencia;
use App\Models\Asistencias\Justificacion;
use App\Models\Asistencias\SalidaTemporal;
use App\Models\Asistencias\SolicitudPermiso;
use App\Models\Asistencias\SolicitudVacaciones;
use App\Models\Nomina\DetallePlanilla;
use App\Models\Sistema\IotEstacion;
use App\Models\Sistema\IotEvento;
use App\Models\Sistema\Parametro;
use App\Services\AlertasService;
use App\Services\AsistenciaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Rol EMPLEADO — "Mi espacio de trabajo" y sus módulos:
 * asistencia y jornada, monitoreo de presencia, historial, alertas,
 * justificaciones, vacaciones y permisos, boletas y perfil.
 */
class EspacioController extends Controller
{
    use ResuelveEmpleado;

    public function __construct(
        private AsistenciaService $asistencias,
        private AlertasService $alertas,
    ) {}

    private function asistenciaDeHoy(?int $empleadoId): ?Asistencia
    {
        if (! $empleadoId) {
            return null;
        }

        return Asistencia::where('empleado_id', $empleadoId)->whereDate('fecha', Carbon::today())->first();
    }

    private function siguientePaso(?Asistencia $a): ?array
    {
        if (! $a || ! $a->hora_entrada) {
            return ['tipo' => 'entrada', 'label' => 'Marcar mi entrada', 'etapa' => 'Jornada sin iniciar'];
        }
        if (! $a->hora_inicio_refrigerio) {
            return ['tipo' => 'refrigerio_inicio', 'label' => 'Iniciar refrigerio', 'etapa' => 'Trabajando'];
        }
        if (! $a->hora_fin_refrigerio) {
            return ['tipo' => 'refrigerio_fin', 'label' => 'Terminar refrigerio', 'etapa' => 'En refrigerio'];
        }
        if (! $a->hora_salida) {
            return ['tipo' => 'salida', 'label' => 'Marcar mi salida', 'etapa' => 'De vuelta del refrigerio'];
        }

        return null;
    }

    private function resumenMes($empleadoId): array
    {
        $desde = Carbon::now()->startOfMonth();
        $filas = Asistencia::where('empleado_id', $empleadoId)
            ->whereBetween('fecha', [$desde->toDateString(), Carbon::today()->toDateString()])->get();

        return [
            'dias' => $filas->whereIn('estado', ['Asistió', 'Tardanza', 'Justificado'])->count(),
            'tardanzas' => $filas->where('tardanza_min', '>', 0)->count(),
            'minutos' => (int) $filas->sum('minutos_trabajados'),
            'ausencias' => $filas->where('estado', 'Ausente')->count(),
            'total' => $filas->count(),
        ];
    }

    private function salidaActiva($empleadoId): ?SalidaTemporal
    {
        return SalidaTemporal::where('empleado_id', $empleadoId)
            ->where('estado', SalidaTemporal::ESTADO_ACTIVA)->latest('hora_salida')->first();
    }

    public function inicio()
    {
        $empleado = $this->empleadoActual();
        if (! $empleado) {
            return redirect()->route('perfil')->with('error', 'Su cuenta no está vinculada a un expediente de trabajador. Solicite la vinculación al Administrador.');
        }

        $asistencia = $this->asistenciaDeHoy($empleado->id);
        $siguiente = $this->siguientePaso($asistencia);
        $resumen = $this->resumenMes($empleado->id);
        $salida = $this->salidaActiva($empleado->id);

        $historial = Asistencia::where('empleado_id', $empleado->id)
            ->orderByDesc('fecha')->limit(6)->get();

        $boleta = DetallePlanilla::with('periodo')->where('empleado_id', $empleado->id)
            ->orderByDesc('id')->first();

        $vacaciones = SolicitudVacaciones::where('empleado_id', $empleado->id)
            ->where('estado', 'Aprobada')->get();

        $estacion = IotEstacion::where('empleado_id', $empleado->id)->where('activo', true)->first();

        return view('espacio.inicio', [
            'empleado' => $empleado,
            'asistencia' => $asistencia,
            'siguiente' => $siguiente,
            'resumen' => $resumen,
            'salida' => $salida,
            'historial' => $historial,
            'boleta' => $boleta,
            'diasVacaciones' => (int) $vacaciones->sum('dias'),
            'estacion' => $estacion,
            'alertas' => $this->alertas->paraEmpleado($empleado, 4),
            'eventos' => IotEvento::where('empleado_id', $empleado->id)->latest('detectado_en')->limit(3)->get(),
        ]);
    }

    public function asistencia()
    {
        $empleado = $this->empleadoActual();
        abort_unless($empleado, 403, 'Su cuenta no está vinculada a un expediente de trabajador.');

        $a = $this->asistenciaDeHoy($empleado->id);
        $salida = $this->salidaActiva($empleado->id);
        $estacion = IotEstacion::where('empleado_id', $empleado->id)->where('activo', true)->first();

        return view('espacio.asistencia', [
            'empleado' => $empleado,
            'asistencia' => $a,
            'siguiente' => $this->siguientePaso($a),
            'salida' => $salida,
            'estacion' => $estacion,
            'tolerancia' => (int) Parametro::valor('tolerancia_ausencia', 5),
            'refrigerio' => (int) Parametro::valor('refrigerio_min', 45),
            'jornada' => $empleado->calendario,
            'historial' => Asistencia::where('empleado_id', $empleado->id)->orderByDesc('fecha')->limit(5)->get(),
            'eventos' => IotEvento::where('empleado_id', $empleado->id)->latest('detectado_en')->limit(4)->get(),
        ]);
    }

    /** Marcaciones del empleado: entrada, refrigerio, retorno y salida. */
    public function marcar(Request $request)
    {
        $empleado = $this->empleadoActual();
        abort_unless($empleado, 403);

        $datos = $request->validate(['tipo' => 'required|in:entrada,refrigerio_inicio,refrigerio_fin,salida']);
        $a = Asistencia::firstOrNew(['empleado_id' => $empleado->id, 'fecha' => Carbon::today()->toDateString()]);
        $ahora = Carbon::now()->format('H:i:s');

        match ($datos['tipo']) {
            'entrada' => $a->hora_entrada = $a->hora_entrada ?: $ahora,
            'refrigerio_inicio' => $a->hora_inicio_refrigerio = $a->hora_inicio_refrigerio ?: $ahora,
            'refrigerio_fin' => $a->hora_fin_refrigerio = $a->hora_fin_refrigerio ?: $ahora,
            'salida' => $a->hora_salida = $a->hora_salida ?: $ahora,
        };
        $a->origen = 'Web';
        $a->setRelation('empleado', $empleado);
        $this->asistencias->calcular($a);
        $a->save();

        return back()->with('ok', 'Marcación registrada: '.$ahora);
    }

    public function monitoreo()
    {
        $empleado = $this->empleadoActual();
        abort_unless($empleado, 403);

        $estacion = IotEstacion::where('empleado_id', $empleado->id)->first()
            ?? IotEstacion::where('tipo', 'camara')->first();

        return view('espacio.monitoreo', [
            'empleado' => $empleado,
            'estacion' => $estacion,
            'salida' => $this->salidaActiva($empleado->id),
            'tolerancia' => (int) Parametro::valor('tolerancia_ausencia', 5),
            'monitoreoActivo' => (bool) Parametro::valor('monitoreo_iot', 1),
            'deteccionCelular' => (bool) Parametro::valor('deteccion_celular', 1),
            'eventos' => IotEvento::with('estacion')->where('empleado_id', $empleado->id)->latest('detectado_en')->limit(8)->get(),
        ]);
    }

    public function historial(Request $request)
    {
        $empleado = $this->empleadoActual();
        abort_unless($empleado, 403);

        $desde = $request->filled('desde') ? Carbon::parse($request->desde) : Carbon::now()->startOfMonth();
        $hasta = $request->filled('hasta') ? Carbon::parse($request->hasta) : Carbon::today();

        $filas = Asistencia::where('empleado_id', $empleado->id)
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->orderByDesc('fecha')->get();

        $salidas = SalidaTemporal::where('empleado_id', $empleado->id)
            ->whereBetween('hora_salida', [$desde->copy()->startOfDay(), $hasta->copy()->endOfDay()])
            ->orderByDesc('hora_salida')->get();

        return view('espacio.historial', [
            'empleado' => $empleado,
            'filas' => $filas,
            'salidas' => $salidas,
            'desde' => $desde, 'hasta' => $hasta,
            'dias' => $filas->whereIn('estado', ['Asistió', 'Tardanza', 'Justificado'])->count(),
            'tardanzas' => $filas->where('tardanza_min', '>', 0)->count(),
            'minutos' => (int) $filas->sum('minutos_trabajados'),
            'refrigerios' => (int) $filas->sum('refrigerio_min'),
            'meses' => Asistencia::where('empleado_id', $empleado->id)->orderByDesc('fecha')->limit(60)->get(),
        ]);
    }

    public function alertas()
    {
        $empleado = $this->empleadoActual();
        abort_unless($empleado, 403);

        return view('espacio.alertas', [
            'empleado' => $empleado,
            'alertas' => $this->alertas->paraEmpleado($empleado, 20),
        ]);
    }

    public function justificaciones()
    {
        $empleado = $this->empleadoActual();
        abort_unless($empleado, 403);

        return view('espacio.justificaciones', [
            'empleado' => $empleado,
            'justificaciones' => Justificacion::where('empleado_id', $empleado->id)->orderByDesc('fecha')->get(),
            'asistencias' => Asistencia::where('empleado_id', $empleado->id)->orderByDesc('fecha')->limit(20)->get(),
        ]);
    }

    public function guardarJustificacion(Request $request)
    {
        $empleado = $this->empleadoActual();
        abort_unless($empleado, 403);

        $datos = $request->validate([
            'fecha' => 'required|date',
            'motivo' => 'required|string|max:120',
            'horario_afectado' => 'nullable|string|max:60',
            'explicacion' => 'required|string|max:1000',
            'documento' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
        ], [
            'fecha.required' => 'Indique la fecha de la incidencia.',
            'motivo.required' => 'Seleccione el motivo de la justificación.',
            'explicacion.required' => 'Describa el motivo de su justificación.',
        ]);

        // FASE 5: el documento (puede contener información médica) se guarda en
        // disco PRIVADO y se descarga por ruta autenticada; nunca con URL pública.
        if ($request->hasFile('documento')) {
            $datos['documento'] = $request->file('documento')->store('justificaciones', 'expedientes');
        }

        Justificacion::create($datos + [
            'empleado_id' => $empleado->id,
            'estado' => 'Pendiente',
            'comunicante' => $empleado->nombre_completo,
        ]);

        return back()->with('ok', 'Justificación registrada. Quedó pendiente de revisión por su supervisor o RR. HH.');
    }

    /** FASE 5: descarga autenticada del adjunto propio (disco privado 'expedientes'). */
    public function descargarJustificacion(Request $request, Justificacion $justificacion)
    {
        $empleado = $this->empleadoActual();
        abort_unless($empleado, 403);
        abort_unless((int) $justificacion->empleado_id === (int) $empleado->id, 403, 'Sólo puede ver sus propios adjuntos.');
        abort_unless($justificacion->documento && Storage::disk('expedientes')->exists($justificacion->documento), 404);

        return Storage::disk('expedientes')->download($justificacion->documento);
    }

    public function vacaciones()
    {
        $empleado = $this->empleadoActual();
        abort_unless($empleado, 403);

        $aprobadas = SolicitudVacaciones::where('empleado_id', $empleado->id)->where('estado', 'Aprobada')->get();

        return view('espacio.vacaciones', [
            'empleado' => $empleado,
            'vacaciones' => SolicitudVacaciones::where('empleado_id', $empleado->id)->orderByDesc('desde')->get(),
            'permisos' => SolicitudPermiso::where('empleado_id', $empleado->id)->orderByDesc('fecha')->get(),
            'diasDisponibles' => max(0, 30 - (int) $aprobadas->sum('dias')),
            'diasUsados' => (int) $aprobadas->sum('dias'),
            'diasPendientes' => (int) SolicitudVacaciones::where('empleado_id', $empleado->id)->where('estado', 'Pendiente')->sum('dias'),
        ]);
    }

    public function guardarVacaciones(Request $request)
    {
        $empleado = $this->empleadoActual();
        abort_unless($empleado, 403);

        $datos = $request->validate([
            'tipo' => 'required|in:Vacaciones,Permiso,Permiso por salud',
            'desde' => 'required|date|after_or_equal:today',
            'hasta' => 'required|date|after_or_equal:desde',
            'motivo' => 'required|string|max:500',
        ], [
            'desde.after_or_equal' => 'La fecha de inicio no puede ser anterior a hoy.',
            'hasta.after_or_equal' => 'La fecha final debe ser posterior al inicio.',
            'motivo.required' => 'Indique el motivo de la solicitud.',
        ]);

        if ($datos['tipo'] === 'Vacaciones') {
            $dias = Carbon::parse($datos['desde'])->diffInWeekdays(Carbon::parse($datos['hasta'])) + 1;
            SolicitudVacaciones::create([
                'empleado_id' => $empleado->id, 'tipo' => 'Vacaciones',
                'desde' => $datos['desde'], 'hasta' => $datos['hasta'], 'dias' => $dias,
                'estado' => 'Pendiente', 'motivo' => $datos['motivo'],
            ]);
        } else {
            SolicitudPermiso::create([
                'empleado_id' => $empleado->id, 'fecha' => $datos['desde'],
                'hora_desde' => '09:00:00', 'hora_hasta' => '11:00:00',
                'motivo' => $datos['tipo'].': '.$datos['motivo'], 'estado' => 'Pendiente',
            ]);
        }

        return back()->with('ok', 'Solicitud enviada. La aprobación corresponde a su supervisor o a Recursos Humanos.');
    }

    public function boletas()
    {
        $empleado = $this->empleadoActual();
        abort_unless($empleado, 403);

        return view('espacio.boletas', [
            'empleado' => $empleado,
            'detalles' => DetallePlanilla::with('periodo')->where('empleado_id', $empleado->id)
                ->orderByDesc('id')->get(),
        ]);
    }

    public function reportarEmergencia(Request $request)
    {
        $empleado = $this->empleadoActual();
        abort_unless($empleado, 403);

        $datos = $request->validate([
            'motivo' => 'required|string|max:120',
            'lugar' => 'nullable|string|max:120',
            'observacion' => 'nullable|string|max:500',
        ]);

        \App\Models\Asistencias\Emergencia::create($datos + [
            'empleado_id' => $empleado->id,
            'reportado_por' => auth()->user()->name,
            'reportado_por_usuario' => auth()->id(),
            'fecha_hora' => now(),
            'estado' => 'Pendiente',
        ]);

        return back()->with('ok', 'Emergencia reportada de inmediato. No debe esperar el vencimiento de la tolerancia.');
    }
}
