<?php

namespace App\Http\Controllers\Asistencias;

use App\Http\Controllers\Controller;

use App\Models\Empleados\Empleado;
use App\Models\Asistencias\SolicitudVacaciones;
use Illuminate\Http\Request;

class VacacionesController extends Controller
{
    public function index(Request $request)
    {
        $u = $request->user();
        // El empleado sólo ve sus propios registros
        $scope = $u->rol === 'EMPLEADO' ? $u->empleado_id : null;
        $q = SolicitudVacaciones::query()->with('empleado');
        $q->when($scope, fn ($qq) => $qq->where('empleado_id', $scope));

        if ($request->filled('estado')) {
            $q->where('estado', $request->query('estado'));
        }

        return view('vacaciones.index', [
            'solicitudes' => $q->orderByDesc('desde')->paginate(20)->withQueryString(),
            'empleados' => Empleado::orderBy('nombre_completo')->pluck('nombre_completo', 'id'),
            'pendientes' => SolicitudVacaciones::where('estado', 'Pendiente')->count(),
            'diasAprobados' => (float) SolicitudVacaciones::where('estado', 'Aprobada')->sum('dias'),
        ]);
    }

    public function create()
    {
        return view('vacaciones.create', [
            'solicitud' => new SolicitudVacaciones(['desde' => now()->toDateString(), 'hasta' => now()->addDays(6)->toDateString(), 'tipo' => 'Vacaciones']),
            'empleados' => Empleado::orderBy('nombre_completo')->pluck('nombre_completo', 'id'),
        ]);
    }

    public function store(Request $request)
    {
        if ($request->user()->rol === 'EMPLEADO') {
            $request->merge(['empleado_id' => $request->user()->empleado_id]);
        }

        $datos = $this->validar($request);
        $solicitud = new SolicitudVacaciones($datos);
        $solicitud->dias = $solicitud->calcularDias();
        $solicitud->save();

        return redirect()->route('vacaciones.index')->with('ok', 'Solicitud registrada: '.$solicitud->dias.' días.');
    }

    public function show(SolicitudVacaciones $vacacione)
    {
        $vacacione->load('empleado.departamento');

        return view('vacaciones.show', ['solicitud' => $vacacione]);
    }

    public function edit(SolicitudVacaciones $vacacione)
    {
        return view('vacaciones.edit', [
            'solicitud' => $vacacione,
            'empleados' => Empleado::orderBy('nombre_completo')->pluck('nombre_completo', 'id'),
        ]);
    }

    public function update(Request $request, SolicitudVacaciones $vacacione)
    {
        $datos = $this->validar($request);
        $vacacione->fill($datos);
        $vacacione->dias = $vacacione->calcularDias();
        $vacacione->save();

        return redirect()->route('vacaciones.show', $vacacione)->with('ok', 'Solicitud actualizada.');
    }

    public function destroy(SolicitudVacaciones $vacacione)
    {
        $vacacione->delete();

        return redirect()->route('vacaciones.index')->with('ok', 'Solicitud eliminada.');
    }

    public function aprobar(Request $request, SolicitudVacaciones $vacacione)
    {
        $vacacione->update([
            'estado' => $request->input('estado', 'Aprobada'),
            'aprobado_por' => $request->user()->id,
        ]);

        return back()->with('ok', 'Solicitud marcada como '.$vacacione->estado.'.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'tipo' => 'required|string|max:30',
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
            'estado' => 'required|string|max:20',
            'motivo' => 'nullable|string|max:400',
        ], ['hasta.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.']);
    }
}
