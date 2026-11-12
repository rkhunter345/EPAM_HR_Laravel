<?php

namespace App\Http\Controllers\Asistencias;

use App\Http\Controllers\Controller;

use App\Models\Empleados\Empleado;
use App\Models\Asistencias\Justificacion;
use Illuminate\Http\Request;

class JustificacionController extends Controller
{
    public function index(Request $request)
    {
        $u = $request->user();
        // El empleado sólo ve sus propios registros
        $scope = $u->rol === 'EMPLEADO' ? $u->empleado_id : null;
        $q = Justificacion::query()->with(['empleado', 'revisor']);
        $q->when($scope, fn ($qq) => $qq->where('empleado_id', $scope));

        if ($request->filled('estado')) {
            $q->where('estado', $request->query('estado'));
        }
        if ($request->filled('empleado_id')) {
            $q->where('empleado_id', $request->query('empleado_id'));
        }

        return view('justificaciones.index', [
            'justificaciones' => $q->orderByDesc('fecha')->paginate(20)->withQueryString(),
            'empleados' => Empleado::orderBy('nombre_completo')->pluck('nombre_completo', 'id'),
            'pendientes' => Justificacion::where('estado', 'Pendiente')->count(),
            'aprobadas' => Justificacion::where('estado', 'Aprobada')->count(),
            'rechazadas' => Justificacion::where('estado', 'Rechazada')->count(),
        ]);
    }

    public function create()
    {
        return view('justificaciones.create', [
            'justificacion' => new Justificacion(['fecha' => now()->toDateString(), 'estado' => 'Pendiente']),
            'empleados' => Empleado::orderBy('nombre_completo')->pluck('nombre_completo', 'id'),
        ]);
    }

    public function store(Request $request)
    {
        if ($request->user()->rol === 'EMPLEADO') {
            $request->merge(['empleado_id' => $request->user()->empleado_id]);
        }

        // FASE 4: corrección de nombre de clase (tipografía «ustificacion»).
        Justificacion::create($this->validar($request));

        return redirect()->route('justificaciones.index')->with('ok', 'Justificación registrada y enviada a revisión.');
    }

    public function show(Justificacion $justificacion)
    {
        $justificacion->load(['empleado.departamento', 'revisor']);

        return view('justificaciones.show', compact('justificacion'));
    }

    public function edit(Justificacion $justificacion)
    {
        return view('justificaciones.edit', [
            'justificacion' => $justificacion,
            'empleados' => Empleado::orderBy('nombre_completo')->pluck('nombre_completo', 'id'),
        ]);
    }

    public function update(Request $request, Justificacion $justificacion)
    {
        $justificacion->update($this->validar($request));

        return redirect()->route('justificaciones.show', $justificacion)->with('ok', 'Justificación actualizada.');
    }

    public function destroy(Justificacion $justificacion)
    {
        $justificacion->delete();

        return redirect()->route('justificaciones.index')->with('ok', 'Justificación eliminada.');
    }

    public function aprobar(Request $request, Justificacion $justificacion)
    {
        $justificacion->update([
            'estado' => $request->input('estado', 'Aprobada'),
            'revisado_por' => $request->user()->id,
            'observacion' => $request->input('observacion', $justificacion->observacion),
        ]);

        return back()->with('ok', 'Justificación marcada como '.$justificacion->estado.'.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'fecha' => 'required|date',
            'motivo' => 'required|string|max:160',
            'horario_afectado' => 'nullable|string|max:80',
            'comunicante' => 'nullable|string|max:120',
            'explicacion' => 'nullable|string|max:600',
            'estado' => 'required|string|max:30',
        ]);
    }
}
