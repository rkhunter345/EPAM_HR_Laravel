<?php

namespace App\Http\Controllers\Asistencias;

use App\Http\Controllers\Controller;
use App\Models\Asistencias\SalidaTemporal;
use App\Models\Empleados\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SalidaTemporalController extends Controller
{
    public function index()
    {
        $u = Auth::user();
        $esEmpleado = $u->rol === 'EMPLEADO';
        $empleado = $u->empleado_id ? Empleado::find($u->empleado_id) : null;

        $query = SalidaTemporal::with('empleado')->orderByDesc('hora_salida');
        if ($esEmpleado) {
            $query->where('empleado_id', $u->empleado_id);
        }
        $salidas = $query->limit(50)->get();

        $activa = (clone $query)->whereNull('hora_retorno')->first();

        $monitoreo = [
            'estado' => $activa ? 'Monitoreo pausado.' : 'Cámara activa.',
            'detectado' => $activa ? 'No.' : 'Sí.',
            'tolerancia' => '5 minutos.',
            'verificacion' => 'Hace pocos segundos.',
            'zona' => 'Estación de trabajo.',
        ];

        // FASE 2.D: sólo RR. HH. tiene alcance de gestión documentado para
        // registrar salidas de terceros. El supervisor sólo gestiona las suyas
        // (su revisión se limita al panel de su equipo, no a crear salidas ajenas).
        $empleados = $u->rol === 'RRHH' ? Empleado::orderBy('nombre_completo')->get() : collect();

        return view('salidas.index', compact('empleado', 'salidas', 'activa', 'monitoreo', 'empleados', 'esEmpleado'));
    }

    public function store(Request $request)
    {
        $u = Auth::user();
        $reglas = [
            'motivo' => ['required', 'string', 'in:' . implode(',', SalidaTemporal::MOTIVOS)],
            'observacion' => ['nullable', 'string', 'max:500'],
            'documento' => ['nullable', 'file', 'max:4096', 'mimes:pdf,jpg,jpeg,png'],
        ];
        // FASE 2.D: sólo RR. HH. puede elegir empleado_id (alcance de gestión
        // documentado). El supervisor registra únicamente sus propias salidas.
        if ($u->rol === 'RRHH') {
            $reglas['empleado_id'] = ['required', 'integer', 'exists:empleados,id'];
        }
        $datos = $request->validate($reglas);

        $empleadoId = $u->rol === 'RRHH' ? (int) $datos['empleado_id'] : (int) $u->empleado_id;
        if (! $empleadoId) {
            abort(403, 'Su cuenta no está vinculada a un expediente de trabajador.');
        }

        $activa = SalidaTemporal::where('empleado_id', $empleadoId)->whereNull('hora_retorno')->first();
        if ($activa) {
            return back()->with('error', 'Ya existe una salida temporal activa. Debe regresar al puesto antes de registrar otra.');
        }

        // FASE 5: documento en disco privado (nunca públicamente accesible).
        $ruta = null;
        if ($request->hasFile('documento')) {
            $ruta = $request->file('documento')->store('salidas-temporales', 'expedientes');
        }

        SalidaTemporal::create([
            'empleado_id' => $empleadoId,
            'motivo' => $datos['motivo'],
            'observacion' => $datos['observacion'] ?? null,
            'documento' => $ruta,
            'hora_salida' => now(),
            'estado' => SalidaTemporal::ESTADO_ACTIVA,
        ]);

        return back()->with('ok', 'Salida temporal registrada. El monitoreo de presencia se pausó.');
    }

    public function regresar(Request $request, SalidaTemporal $salida)
    {
        $u = Auth::user();
        // FASE 2.D: el empleado sólo cierra su propia salida. RR. HH. tiene
        // alcance de gestión documentado; el supervisor no cierra salidas ajenas.
        $permitidoTerceros = $u->rol === 'RRHH';
        if (! $permitidoTerceros && (int) $salida->empleado_id !== (int) $u->empleado_id) {
            abort(403, 'No puede modificar la salida temporal de otro empleado.');
        }
        if ($salida->hora_retorno) {
            return back()->with('error', 'Esta salida temporal ya fue cerrada.');
        }
        $salida->update(['hora_retorno' => now(), 'estado' => SalidaTemporal::ESTADO_COMPLETADA]);

        return back()->with('ok', 'Regreso registrado. El monitoreo de presencia se reactivó.');
    }

    /** FASE 5: descarga autenticada del adjunto, con control de propiedad/rol. */
    public function descargarAdjunto(Request $request, SalidaTemporal $salida)
    {
        $u = $request->user();
        $permitido = (int) $salida->empleado_id === (int) $u->empleado_id || $u->rol === 'RRHH';
        abort_unless($permitido, 403, 'No tiene autorización para ver este documento.');
        abort_unless($salida->documento && Storage::disk('expedientes')->exists($salida->documento), 404);

        return Storage::disk('expedientes')->download($salida->documento);
    }
}
