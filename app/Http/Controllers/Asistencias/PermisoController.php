<?php

namespace App\Http\Controllers\Asistencias;

use App\Http\Controllers\Controller;

use App\Models\Empleados\Empleado;
use App\Models\Asistencias\SolicitudPermiso;
use Illuminate\Http\Request;

class PermisoController extends Controller
{
    public function index(Request $request)
    {
        $u = $request->user();
        // El empleado sólo ve sus propios registros
        $scope = $u->rol === 'EMPLEADO' ? $u->empleado_id : null;
        $q = SolicitudPermiso::query()->with('empleado');
        $q->when($scope, fn ($qq) => $qq->where('empleado_id', $scope));

        if ($request->filled('estado')) {
            $q->where('estado', $request->query('estado'));
        }

        return view('permisos.index', [
            'permisos' => $q->orderByDesc('fecha')->paginate(20)->withQueryString(),
            'empleados' => Empleado::orderBy('nombre_completo')->pluck('nombre_completo', 'id'),
            'pendientes' => SolicitudPermiso::where('estado', 'Pendiente')->count(),
        ]);
    }

    public function create()
    {
        return view('permisos.create', [
            'permiso' => new SolicitudPermiso(['fecha' => now()->toDateString(), 'estado' => 'Pendiente']),
            'empleados' => Empleado::orderBy('nombre_completo')->pluck('nombre_completo', 'id'),
        ]);
    }

    public function store(Request $request)
    {
        if ($request->user()->rol === 'EMPLEADO') {
            $request->merge(['empleado_id' => $request->user()->empleado_id]);
        }

        olicitudPermiso::create($this->validar($request));

        return redirect()->route('permisos.index')->with('ok', 'Permiso registrado.');
    }

    public function show(SolicitudPermiso $permiso)
    {
        $permiso->load('empleado.departamento');

        return view('permisos.show', compact('permiso'));
    }

    public function edit(SolicitudPermiso $permiso)
    {
        return view('permisos.edit', [
            'permiso' => $permiso,
            'empleados' => Empleado::orderBy('nombre_completo')->pluck('nombre_completo', 'id'),
        ]);
    }

    public function update(Request $request, SolicitudPermiso $permiso)
    {
        $permiso->update($this->validar($request));

        return redirect()->route('permisos.show', $permiso)->with('ok', 'Permiso actualizado.');
    }

    public function destroy(SolicitudPermiso $permiso)
    {
        $permiso->delete();

        return redirect()->route('permisos.index')->with('ok', 'Permiso eliminado.');
    }

    public function aprobar(Request $request, SolicitudPermiso $permiso)
    {
        $permiso->update([
            'estado' => $request->input('estado', 'Aprobada'),
            'aprobado_por' => $request->user()->id,
        ]);

        return back()->with('ok', 'Permiso marcado como '.$permiso->estado.'.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'fecha' => 'required|date',
            'hora_desde' => 'nullable',
            'hora_hasta' => 'nullable',
            'motivo' => 'required|string|max:200',
            'estado' => 'required|string|max:20',
        ]);
    }
}
