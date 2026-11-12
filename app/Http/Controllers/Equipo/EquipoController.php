<?php

namespace App\Http\Controllers\Equipo;

use App\Http\Controllers\Concerns\ResuelveEmpleado;
use App\Http\Controllers\Controller;
use App\Models\Asistencias\Asistencia;
use App\Models\Asistencias\Emergencia;
use App\Models\Asistencias\Justificacion;
use App\Models\Asistencias\SolicitudPermiso;
use App\Models\Asistencias\SolicitudVacaciones;
use App\Models\Sistema\IotEvento;
use App\Models\Sistema\Parametro;
use App\Services\AlertasService;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Rol SUPERVISOR — panel de supervisión del equipo asignado.
 * Regla del documento: el supervisor no puede resolver sus propios casos.
 */
class EquipoController extends Controller
{
    use ResuelveEmpleado;

    public function __construct(private AlertasService $alertas) {}

    private function esPropio($empleadoId): bool
    {
        return (int) $this->empleadoActual()?->id === (int) $empleadoId;
    }

    public function panel()
    {
        $equipo = $this->equipoDelSupervisor();
        $ids = $equipo->pluck('id');
        $hoy = Carbon::today();

        $hoyFilas = Asistencia::with('empleado')->whereIn('empleado_id', $ids)->whereDate('fecha', $hoy)->get();

        return view('equipo.panel', [
            'equipo' => $equipo,
            'miEmpleado' => $this->empleadoActual(),
            'presentes' => $hoyFilas->whereIn('estado', ['Asistió', 'Tardanza', 'Justificado'])->count(),
            'ausentes' => $equipo->count() - $hoyFilas->count() + $hoyFilas->where('estado', 'Ausente')->count(),
            'alertasIot' => IotEvento::whereIn('empleado_id', $ids)->whereDate('detectado_en', $hoy)->where('estado', 'Registrado')->count(),
            'tardanzas' => $hoyFilas->where('tardanza_min', '>', 0)->count(),
            'solicitudes' => Justificacion::whereIn('empleado_id', $ids)->where('estado', 'Pendiente')->count()
                + SolicitudVacaciones::whereIn('empleado_id', $ids)->where('estado', 'Pendiente')->count()
                + SolicitudPermiso::whereIn('empleado_id', $ids)->where('estado', 'Pendiente')->count(),
            'alertas' => $this->alertas->paraEquipo($equipo, 6),
            'filas' => $hoyFilas->take(8),
            'miAsistencia' => $this->empleadoActual()
                ? Asistencia::where('empleado_id', $this->empleadoActual()->id)->whereDate('fecha', $hoy)->first() : null,
            'miResumen' => $this->empleadoActual()
                ? Asistencia::where('empleado_id', $this->empleadoActual()->id)->whereBetween('fecha', [Carbon::now()->startOfMonth()->toDateString(), $hoy->toDateString()])->get() : collect(),
        ]);
    }

    public function asistencia(Request $request)
    {
        $equipo = $this->equipoDelSupervisor();
        $ids = $equipo->pluck('id');
        $fecha = $request->filled('fecha') ? Carbon::parse($request->fecha) : Carbon::today();

        return view('equipo.asistencia', [
            'equipo' => $equipo,
            'filas' => Asistencia::with('empleado.departamento')->whereIn('empleado_id', $ids)
                ->whereDate('fecha', $fecha)->orderByDesc('fecha')->get(),
            'fecha' => $fecha,
            'miEmpleado' => $this->empleadoActual(),
            'salidas' => \App\Models\Asistencias\SalidaTemporal::with('empleado')->whereIn('empleado_id', $ids)
                ->whereDate('hora_salida', $fecha)->get(),
        ]);
    }

    public function monitoreo()
    {
        $equipo = $this->equipoDelSupervisor();
        $ids = $equipo->pluck('id');

        return view('equipo.monitoreo', [
            'equipo' => $equipo,
            'eventos' => IotEvento::with(['empleado', 'estacion'])->whereIn('empleado_id', $ids)
                ->latest('detectado_en')->limit(12)->get(),
            'tolerancia' => (int) Parametro::valor('tolerancia_ausencia', 5),
            'miEmpleado' => $this->empleadoActual(),
            'salidas' => \App\Models\Asistencias\SalidaTemporal::with('empleado')->whereIn('empleado_id', $ids)
                ->where('estado', \App\Models\Asistencias\SalidaTemporal::ESTADO_ACTIVA)->get(),
        ]);
    }

    public function solicitudes(Request $request)
    {
        $equipo = $this->equipoDelSupervisor();
        $ids = $equipo->pluck('id');

        return view('equipo.solicitudes', [
            'justificaciones' => Justificacion::with('empleado')->whereIn('empleado_id', $ids)->orderByDesc('fecha')->get(),
            'vacaciones' => SolicitudVacaciones::with('empleado')->whereIn('empleado_id', $ids)->orderByDesc('desde')->get(),
            'permisos' => SolicitudPermiso::with('empleado')->whereIn('empleado_id', $ids)->orderByDesc('fecha')->get(),
            'empleadoPropio' => $this->empleadoActual(),
            'pestana' => $request->get('tab', 'justificaciones'),
        ]);
    }

    /** Aprobación/rechazo con trazabilidad, bloqueo de casos propios y control de ámbito. */
    public function revisar(Request $request, string $tipo, int $id)
    {
        $datos = $request->validate([
            'decision' => 'required|in:Aprobada,Rechazada,Información solicitada',
            'observacion' => 'nullable|string|max:500',
        ]);

        $modelo = match ($tipo) {
            'justificacion' => Justificacion::findOrFail($id),
            'vacaciones' => SolicitudVacaciones::findOrFail($id),
            'permiso' => SolicitudPermiso::findOrFail($id),
        };

        if ($this->esPropio($modelo->empleado_id)) {
            return back()->with('error', 'No puede revisar ni aprobar sus propios casos. El caso debe derivarse a Recursos Humanos.');
        }

        // FASE 2.C: el caso debe pertenecer al equipo asignado al supervisor.
        if (! $this->idsEquipoSupervisor()->contains((int) $modelo->empleado_id)) {
            abort(403, 'El caso no pertenece a su equipo asignado.');
        }

        $modelo->estado = $datos['decision'];
        $modelo->observacion = $datos['observacion'] ?? null;
        if (in_array($tipo, ['vacaciones'], true)) {
            $modelo->aprobado_por = auth()->id();
        } elseif ($tipo === 'justificacion') {
            $modelo->revisado_por = auth()->id();
        } else {
            $modelo->aprobado_por = auth()->id();
        }
        $modelo->save();

        return back()->with('ok', 'Decisión registrada: '.$datos['decision'].' (responsable: '.auth()->user()->name.').');
    }

    public function reportes(Request $request)
    {
        $equipo = $this->equipoDelSupervisor();
        $ids = $equipo->pluck('id');
        $desde = $request->filled('desde') ? Carbon::parse($request->desde) : Carbon::now()->startOfMonth();
        $hasta = $request->filled('hasta') ? Carbon::parse($request->hasta) : Carbon::today();

        $filas = Asistencia::with('empleado')->whereIn('empleado_id', $ids)
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])->get();

        $total = max(1, $filas->count());

        return view('equipo.reportes', [
            'equipo' => $equipo, 'desde' => $desde, 'hasta' => $hasta,
            'asistencia' => round($filas->whereIn('estado', ['Asistió', 'Tardanza'])->count() / $total * 100),
            'tardanzas' => $filas->where('tardanza_min', '>', 0)->count(),
            'ausencias' => $filas->where('estado', 'Ausente')->count(),
            'justificados' => $filas->where('estado', 'Justificado')->count(),
            'minutos' => (int) $filas->sum('minutos_trabajados'),
            'porEmpleado' => $equipo->map(function ($e) use ($filas) {
                $m = $filas->where('empleado_id', $e->id);
                return [
                    'empleado' => $e,
                    'tardanzas' => $m->where('tardanza_min', '>', 0)->count(),
                    'ausencias' => $m->where('estado', 'Ausente')->count(),
                    'puntualidad' => $m->count() ? round($m->where('tardanza_min', 0)->where('estado', '!=', 'Ausente')->count() / $m->count() * 100) : 0,
                ];
            }),
            'serie' => $this->serie($filas),
        ]);
    }

    private function serie($filas): array
    {
        $serie = [];
        foreach ($filas->groupBy(fn ($f) => Carbon::parse($f->fecha)->format('d/m'))->sortKeys()->take(-6) as $dia => $grupo) {
            $serie[] = [
                'etiqueta' => $dia,
                'asistio' => $grupo->whereIn('estado', ['Asistió', 'Tardanza'])->count(),
                'tardanza' => $grupo->where('tardanza_min', '>', 0)->count(),
                'ausente' => $grupo->where('estado', 'Ausente')->count(),
            ];
        }

        return $serie;
    }
}
