<?php

namespace App\Http\Controllers\Asistencias;

use App\Http\Controllers\Controller;

use App\Models\Asistencias\Asistencia;
use App\Models\Empleados\Empleado;
use App\Services\AsistenciaService;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    public function __construct(private AsistenciaService $servicio) {}

    public function index(Request $request)
    {
        $u = $request->user();
        $q = Asistencia::query()->with('empleado');

        // El empleado sólo ve sus marcaciones; el resto consulta todo.
        if ($u->rol === 'EMPLEADO') {
            $q->where('empleado_id', $u->empleado_id);
        }

        if ($request->filled('empleado_id')) {
            $q->where('empleado_id', $request->query('empleado_id'));
        }
        if ($request->filled('estado')) {
            $q->where('estado', $request->query('estado'));
        }
        $desde = $request->query('desde') ?: now()->startOfMonth()->toDateString();
        $hasta = $request->query('hasta') ?: now()->toDateString();
        $q->whereBetween('fecha', [$desde, $hasta]);

        $asistencias = $q->orderByDesc('fecha')->paginate(25)->withQueryString();
        $resumen = $this->servicio->resumenPeriodo($desde, $hasta);

        return view('asistencias.index', [
            'asistencias' => $asistencias,
            'empleados' => Empleado::orderBy('nombre_completo')->pluck('nombre_completo', 'id'),
            'resumen' => $resumen,
            'puntualidad' => $this->servicio->puntualidad($resumen),
            'desde' => $desde,
            'hasta' => $hasta,
        ]);
    }

    public function create()
    {
        return view('asistencias.create', [
            'asistencia' => new Asistencia(['fecha' => now()->toDateString(), 'estado' => 'Asistió', 'origen' => 'Web']),
            'empleados' => Empleado::where('estado', 'Activo')->orderBy('nombre_completo')->pluck('nombre_completo', 'id'),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        if ($request->user()->rol === 'EMPLEADO') {
            $datos['empleado_id'] = $request->user()->empleado_id; // siempre su propia marcación
        }
        $asistencia = new Asistencia($datos);
        $asistencia->empleado_id = $datos['empleado_id'];
        $asistencia->load('empleado');
        $this->servicio->calcular($asistencia);
        $asistencia->save();

        return redirect()->route('asistencias.index')->with('ok', 'Marcación registrada y calculada.');
    }

    public function show(Asistencia $asistencia)
    {
        $asistencia->load('empleado.departamento');

        return view('asistencias.show', compact('asistencia'));
    }

    public function edit(Asistencia $asistencia)
    {
        return view('asistencias.edit', [
            'asistencia' => $asistencia,
            'empleados' => Empleado::orderBy('nombre_completo')->pluck('nombre_completo', 'id'),
        ]);
    }

    public function update(Request $request, Asistencia $asistencia)
    {
        $asistencia->fill($this->validar($request));
        $asistencia->load('empleado');
        $this->servicio->calcular($asistencia);
        $asistencia->save();

        return redirect()->route('asistencias.index')->with('ok', 'Marcación actualizada.');
    }

    public function destroy(Asistencia $asistencia)
    {
        $asistencia->delete();

        return redirect()->route('asistencias.index')->with('ok', 'Marcación eliminada.');
    }

    /** Acción rápida: marcar entrada, inicio/fin de refrigerio o salida del día. */
    public function marcar(Request $request)
    {
        $datos = $request->validate([
            'empleado_id' => 'nullable|exists:empleados,id',
            'tipo' => 'required|in:entrada,refrigerio_inicio,refrigerio_fin,salida',
            'origen' => 'nullable|in:Web,Móvil,QR,IoT',
        ]);

        // Desde el tablero el empleado se marca a sí mismo con un clic: el
        // servidor siempre decide el empleado_id por el usuario autenticado.
        if ($request->user()->rol === 'EMPLEADO') {
            $empleadoId = $request->user()->empleado_id;
        } else {
            $empleadoId = $datos['empleado_id'] ?? null;
        }

        if (! $empleadoId) {
            return back()->with('error', 'Su usuario no tiene un empleado vinculado; seleccione el trabajador a marcar.');
        }

        $asistencia = Asistencia::where('empleado_id', $empleadoId)
            ->whereDate('fecha', now()->toDateString())->first()
            ?? new Asistencia(['empleado_id' => $empleadoId, 'fecha' => now()->toDateString()]);
        $asistencia->origen = $datos['origen'] ?? 'Web';

        $campo = match ($datos['tipo']) {
            'entrada' => 'hora_entrada',
            'refrigerio_inicio' => 'hora_inicio_refrigerio',
            'refrigerio_fin' => 'hora_fin_refrigerio',
            'salida' => 'hora_salida',
        };

        // Evita sobrescribir una marcación ya registrada (doble clic).
        if ($asistencia->{$campo}) {
            return back()->with('error', 'La marcación de '.str_replace('_', ' ', $datos['tipo']).' ya estaba registrada a las '.substr((string) $asistencia->{$campo}, 0, 5).'.');
        }

        $asistencia->{$campo} = now()->format('H:i:s');

        $asistencia->load('empleado');
        $this->servicio->calcular($asistencia);
        $asistencia->save();

        return back()->with('ok', 'Marcación de '.str_replace('_', ' ', $datos['tipo']).' registrada a las '.now()->format('H:i').'.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'fecha' => 'required|date',
            'hora_entrada' => 'nullable',
            'hora_inicio_refrigerio' => 'nullable',
            'hora_fin_refrigerio' => 'nullable',
            'hora_salida' => 'nullable',
            'estado' => 'nullable|string|max:20',
            'origen' => 'nullable|in:Web,Móvil,QR,IoT',
            'observacion' => 'nullable|string|max:400',
        ]);
    }
}
