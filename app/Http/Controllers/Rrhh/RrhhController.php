<?php

namespace App\Http\Controllers\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\Asistencias\Asistencia;
use App\Models\Asistencias\Emergencia;
use App\Models\Asistencias\Justificacion;
use App\Models\Asistencias\SalidaTemporal;
use App\Models\Asistencias\SolicitudPermiso;
use App\Models\Asistencias\SolicitudVacaciones;
use App\Models\Empleados\Empleado;
use App\Models\Nomina\DetallePlanilla;
use App\Models\Nomina\PeriodoPlanilla;
use App\Models\Organizacion\Departamento;
use App\Models\Sistema\Auditoria;
use App\Models\Sistema\IotEvento;
use App\Models\Sistema\Parametro;
use App\Services\AlertasService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Rol RECURSOS HUMANOS — procesos laborales: personal, asistencia, casos,
 * nómina, boletas y reportes. Sin acceso a configuración técnica ni usuarios.
 */
class RrhhController extends Controller
{
    public function __construct(private AlertasService $alertas) {}

    public function panel()
    {
        $hoy = Carbon::today();
        $mes = Carbon::now()->startOfMonth();
        $filasMes = Asistencia::whereBetween('fecha', [$mes->toDateString(), $hoy->toDateString()])->get();

        return view('rrhh.panel', [
            'totalPersonal' => Empleado::count(),
            'activos' => Empleado::where('estado', 'Activo')->count(),
            'casosPendientes' => Justificacion::where('estado', 'Pendiente')->count()
                + SolicitudVacaciones::where('estado', 'Pendiente')->count()
                + SolicitudPermiso::where('estado', 'Pendiente')->count(),
            'justificacionesPend' => Justificacion::where('estado', 'Pendiente')->count(),
            'emergencias' => Emergencia::with('empleado')->whereIn('estado', ['Pendiente', 'En revisión'])->latest('fecha_hora')->limit(4)->get(),
            'alertasIot' => IotEvento::where('estado', 'Registrado')->whereDate('detectado_en', $hoy)->count(),
            'periodo' => PeriodoPlanilla::orderByDesc('anio')->orderByDesc('mes')->first(),
            'asistenciaHoy' => Asistencia::whereDate('fecha', $hoy)->whereIn('estado', ['Asistió', 'Tardanza'])->count(),
            'tardanzasHoy' => Asistencia::whereDate('fecha', $hoy)->where('tardanza_min', '>', 0)->count(),
            'ausentesHoy' => Asistencia::whereDate('fecha', $hoy)->where('estado', 'Ausente')->count(),
            'tasaAsistencia' => $filasMes->count() ? round($filasMes->whereIn('estado', ['Asistió', 'Tardanza', 'Justificado'])->count() / $filasMes->count() * 100) : 0,
            'porDepartamento' => Departamento::withCount(['empleados' => fn ($q) => $q->where('estado', 'Activo')])->orderByDesc('empleados_count')->get(),
            'vacacionesProximas' => SolicitudVacaciones::with('empleado')->where('estado', 'Aprobada')
                ->whereBetween('desde', [$hoy->toDateString(), $hoy->copy()->addDays(30)->toDateString()])->get(),
            'serie' => $this->serieMensual(),
            'ultimos' => Auditoria::latest('id')->limit(5)->get(),
        ]);
    }

    private function serieMensual(): array
    {
        $serie = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->startOfMonth()->subMonths($i);
            $f = Asistencia::whereYear('fecha', $m->year)->whereMonth('fecha', $m->month)->get();
            $serie[] = [
                'etiqueta' => mb_substr($m->locale('es')->translatedFormat('M'), 0, 3),
                'asistio' => $f->whereIn('estado', ['Asistió', 'Tardanza'])->count(),
                'tardanza' => $f->where('tardanza_min', '>', 0)->count(),
                'ausente' => $f->where('estado', 'Ausente')->count(),
            ];
        }

        return $serie;
    }

    public function personal(Request $request)
    {
        $q = Empleado::with(['departamento', 'designacion', 'tipoContrato', 'tipoEmpleo']);
        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $q->where(fn ($w) => $w->where('nombre_completo', 'like', "%$b%")->orWhere('codigo', 'like', "%$b%")
                ->orWhere('numero_documento', 'like', "%$b%"));
        }
        if ($request->filled('departamento_id')) {
            $q->where('departamento_id', $request->departamento_id);
        }
        if ($request->filled('estado')) {
            $q->where('estado', $request->estado);
        }

        return view('rrhh.personal', [
            'empleados' => $q->orderBy('nombre_completo')->paginate(10)->withQueryString(),
            'departamentos' => Departamento::orderBy('nombre')->get(),
            'total' => Empleado::count(),
            'activos' => Empleado::where('estado', 'Activo')->count(),
            'proceso' => Empleado::where('estado', 'En proceso')->count(),
            'inactivos' => Empleado::whereIn('estado', ['Inactivo', 'Suspendido'])->count(),
        ]);
    }

    public function asistencia(Request $request)
    {
        $desde = $request->filled('desde') ? Carbon::parse($request->desde) : Carbon::today();
        $q = Asistencia::with('empleado.departamento')->whereDate('fecha', $desde);
        if ($request->filled('departamento_id')) {
            $q->whereHas('empleado', fn ($w) => $w->where('departamento_id', $request->departamento_id));
        }
        $filas = $q->orderBy('empleado_id')->get();

        return view('rrhh.asistencia', [
            'filas' => $filas, 'desde' => $desde,
            'departamentos' => Departamento::orderBy('nombre')->get(),
            'presentes' => $filas->whereIn('estado', ['Asistió', 'Tardanza', 'Justificado'])->count(),
            'tardanzas' => $filas->where('tardanza_min', '>', 0)->count(),
            'ausentes' => $filas->where('estado', 'Ausente')->count(),
            'incompletos' => $filas->where('estado', 'Incompleto')->count(),
            'salidas' => SalidaTemporal::with('empleado')->whereDate('hora_salida', $desde)->get(),
        ]);
    }

    /** Corrección trazable de una marcación (exige motivo, guarda valor anterior). */
    public function corregir(Request $request, Asistencia $asistencia)
    {
        $datos = $request->validate([
            'hora_entrada' => 'nullable', 'hora_salida' => 'nullable',
            'motivo' => 'required|string|max:255',
        ], ['motivo.required' => 'La corrección exige un motivo obligatorio.']);

        $antes = $asistencia->only(['hora_entrada', 'hora_salida', 'estado']);
        $asistencia->hora_entrada = $datos['hora_entrada'] ?: $asistencia->hora_entrada;
        $asistencia->hora_salida = $datos['hora_salida'] ?: $asistencia->hora_salida;
        $asistencia->observacion = 'Corrección autorizada: '.$datos['motivo'];
        $asistencia->setRelation('empleado', $asistencia->empleado);
        app(\App\Services\AsistenciaService::class)->calcular($asistencia);
        $asistencia->save();

        Auditoria::create([
            'usuario_id' => auth()->id(), 'usuario' => auth()->user()->name, 'accion' => 'Corrección de marcación',
            'modulo' => 'Asistencia', 'detalle' => 'Empleado '.$asistencia->empleado_id.' · antes '.json_encode($antes).' · motivo: '.$datos['motivo'],
            'ip' => $request->ip(), 'created_at' => now(),
        ]);

        return back()->with('ok', 'Marcación corregida con motivo registrado y auditoría.');
    }

    public function iot()
    {
        return view('rrhh.iot', [
            'eventos' => IotEvento::with(['empleado', 'estacion'])->latest('detectado_en')->limit(20)->get(),
            'pendientes' => IotEvento::where('estado', 'Registrado')->count(),
            'tolerancia' => IotEvento::where('tipo', 'ausencia_puesto')->count(),
            'registradosHoy' => IotEvento::whereDate('detectado_en', Carbon::today())->count(),
            'revisados' => IotEvento::where('estado', 'Revisado')->count(),
            'descartados' => IotEvento::where('estado', 'Descartado')->count(),
            'estaciones' => \App\Models\Sistema\IotEstacion::with(['empleado', 'sucursal'])->get(),
            'salidas' => SalidaTemporal::with('empleado')->where('estado', SalidaTemporal::ESTADO_ACTIVA)->get(),
        ]);
    }

    public function justificaciones()
    {
        return view('rrhh.justificaciones', [
            'justificaciones' => Justificacion::with('empleado.departamento')->orderByDesc('fecha')->get(),
            'pendientes' => Justificacion::where('estado', 'Pendiente')->count(),
            'aprobadas' => Justificacion::where('estado', 'Aprobada')->count(),
            'rechazadas' => Justificacion::where('estado', 'Rechazada')->count(),
        ]);
    }

    public function emergencias()
    {
        return view('rrhh.emergencias', [
            'emergencias' => Emergencia::with('empleado')->orderByDesc('fecha_hora')->get(),
            'pendientes' => Emergencia::where('estado', 'Pendiente')->count(),
            'enRevision' => Emergencia::where('estado', 'En revisión')->count(),
            'cerradas' => Emergencia::whereIn('estado', ['Aprobada', 'Rechazada'])->count(),
            'delMes' => Emergencia::whereMonth('fecha_hora', Carbon::now()->month)->count(),
        ]);
    }

    public function vacaciones()
    {
        return view('rrhh.vacaciones', [
            'vacaciones' => SolicitudVacaciones::with('empleado.departamento')->orderByDesc('desde')->get(),
            'permisos' => SolicitudPermiso::with('empleado.departamento')->orderByDesc('fecha')->get(),
            'pendientes' => SolicitudVacaciones::where('estado', 'Pendiente')->count(),
            'aprobadas' => SolicitudVacaciones::where('estado', 'Aprobada')->count(),
            'diasOtorgados' => (int) SolicitudVacaciones::where('estado', 'Aprobada')->sum('dias'),
            'aprobadasMes' => SolicitudVacaciones::where('estado', 'Aprobada')->whereMonth('desde', Carbon::now()->month)->count(),
        ]);
    }

    public function nomina(Request $request)
    {
        $periodos = PeriodoPlanilla::orderByDesc('anio')->orderByDesc('mes')->get();
        $periodo = $request->filled('periodo_id')
            ? PeriodoPlanilla::find($request->periodo_id)
            : $periodos->first();

        return view('rrhh.nomina', [
            'periodos' => $periodos,
            'periodo' => $periodo,
            'detalles' => $periodo ? DetallePlanilla::with('empleado.departamento')->where('periodo_planilla_id', $periodo->id)->get() : collect(),
        ]);
    }

    public function boletas(Request $request)
    {
        $periodos = PeriodoPlanilla::orderByDesc('anio')->orderByDesc('mes')->get();
        $periodo = $request->filled('periodo_id') ? PeriodoPlanilla::find($request->periodo_id) : $periodos->first();
        $detalles = $periodo ? DetallePlanilla::with('empleado')->where('periodo_planilla_id', $periodo->id)->get() : collect();

        return view('rrhh.boletas', [
            'periodos' => $periodos, 'periodo' => $periodo, 'detalles' => $detalles,
            'generadas' => $detalles->count(),
            'publicadas' => $periodo && $periodo->estado === 'Pagado' ? $detalles->count() : 0,
            'pendientes' => $periodo ? max(0, Empleado::where('estado', 'Activo')->count() - $detalles->count()) : 0,
            'incidencias' => $detalles->where('neto', '<=', 0)->count(),
        ]);
    }

    public function reportes(Request $request)
    {
        $desde = $request->filled('desde') ? Carbon::parse($request->desde) : Carbon::now()->startOfMonth();
        $hasta = $request->filled('hasta') ? Carbon::parse($request->hasta) : Carbon::today();
        $filas = Asistencia::with('empleado.departamento')
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])->get();
        $total = max(1, $filas->count());

        return view('rrhh.reportes', [
            'desde' => $desde, 'hasta' => $hasta, 'filas' => $filas,
            'departamentos' => Departamento::withCount('empleados')->get(),
            'asistencia' => round($filas->whereIn('estado', ['Asistió', 'Tardanza'])->count() / $total * 100),
            'tardanzas' => $filas->where('tardanza_min', '>', 0)->count(),
            'ausencias' => $filas->where('estado', 'Ausente')->count(),
            'horas' => round($filas->sum('minutos_trabajados') / 60),
            'nomina' => PeriodoPlanilla::orderByDesc('anio')->orderByDesc('mes')->limit(6)->get(),
            'serie' => $this->serieDe($filas),
        ]);
    }

    private function serieDe($filas): array
    {
        $out = [];
        foreach ($filas->groupBy(fn ($f) => Carbon::parse($f->fecha)->format('d/m'))->sortKeys()->take(-7) as $dia => $g) {
            $out[] = ['etiqueta' => $dia, 'asistio' => $g->whereIn('estado', ['Asistió', 'Tardanza'])->count(),
                'tardanza' => $g->where('tardanza_min', '>', 0)->count(), 'ausente' => $g->where('estado', 'Ausente')->count()];
        }

        return $out;
    }

    public function revisar(Request $request, string $tipo, int $id)
    {
        $datos = $request->validate([
            'decision' => 'required|in:Aprobada,Rechazada,Información solicitada,En revisión',
            'observacion' => 'nullable|string|max:500',
        ]);

        $modelo = match ($tipo) {
            'justificacion' => Justificacion::findOrFail($id),
            'vacaciones' => SolicitudVacaciones::findOrFail($id),
            'permiso' => SolicitudPermiso::findOrFail($id),
            'emergencia' => Emergencia::findOrFail($id),
            'iot' => IotEvento::findOrFail($id),
        };

        if ($tipo === 'iot') {
            $modelo->estado = $datos['decision'] === 'Rechazada' ? 'Descartado' : 'Revisado';
            $modelo->observacion = $datos['observacion'] ?? null;
            $modelo->save();

            return back()->with('ok', 'Evento IoT revisado. Se registró al responsable; no constituye una sanción automática.');
        }

        if ((int) auth()->user()->empleado_id === (int) $modelo->empleado_id) {
            return back()->with('error', 'No puede resolver sus propios casos. Debe derivarlo a otro responsable autorizado.');
        }

        $modelo->estado = $datos['decision'];
        $modelo->observacion = $datos['observacion'] ?? null;
        if ($tipo === 'justificacion') {
            $modelo->revisado_por = auth()->id();
        } elseif ($tipo === 'emergencia') {
            $modelo->revisado_por = auth()->id();
            $modelo->revisado_en = now();
        } else {
            $modelo->aprobado_por = auth()->id();
        }
        $modelo->save();

        Auditoria::create([
            'usuario_id' => auth()->id(), 'usuario' => auth()->user()->name,
            'accion' => 'Revisión de '.$tipo, 'modulo' => 'RR. HH.',
            'detalle' => 'Caso '.$id.' · '.$datos['decision'].' · '.($datos['observacion'] ?? 'sin observación'),
            'ip' => $request->ip(), 'created_at' => now(),
        ]);

        return back()->with('ok', 'Caso actualizado: '.$datos['decision'].'.');
    }

    /** FASE 5: descarga autenticada del adjunto de una justificación (disco privado). */
    public function adjuntoJustificacion(Request $request, Justificacion $justificacion)
    {
        abort_unless($justificacion->documento
            && \Illuminate\Support\Facades\Storage::disk('expedientes')->exists($justificacion->documento), 404);

        return \Illuminate\Support\Facades\Storage::disk('expedientes')->download($justificacion->documento);
    }
}
