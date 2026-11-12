<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Empleados\Empleado;
use App\Models\Sistema\IotEstacion;
use App\Models\Sistema\IotEvento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Módulo IoT (andamio funcional para entrenamiento posterior).
 * Recepción de eventos de las estaciones/cámaras por API con token
 * (config epam.iot.token, header X-IOT-TOKEN). Las detecciones se
 * entrenarán después; por ahora el módulo registra, lista y permite
 * revisar o descartar eventos.
 */
class IotController extends Controller
{
    public function index(): View
    {
        return view('iot.index', [
            'estaciones' => IotEstacion::with(['empleado', 'sucursal'])
                ->withCount('eventos')->orderBy('nombre')->get(),
            'eventos' => IotEvento::with(['empleado', 'estacion'])
                ->orderByDesc('detectado_en')->limit(30)->get(),
            'porTipo' => IotEvento::selectRaw('tipo, count(*) as total')
                ->whereBetween('detectado_en', [now()->startOfDay(), now()->endOfDay()])
                ->groupBy('tipo')->pluck('total', 'tipo'),
        ]);
    }

    public function crearEstacion(): View
    {
        return view('iot.estacion-form', [
            'estacion' => new IotEstacion(['tipo' => 'camara', 'activo' => true]),
            'empleados' => Empleado::orderBy('nombre_completo')->pluck('nombre_completo', 'id'),
        ]);
    }

    public function guardarEstacion(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:60|unique:iot_estaciones,nombre',
            'ubicacion' => 'nullable|string|max:120',
            'empleado_id' => 'nullable|exists:empleados,id',
            'tipo' => 'required|in:camara,qr',
            'activo' => 'nullable|boolean',
        ]);
        IotEstacion::create($datos);

        return redirect()->route('iot.index')->with('ok', 'Estación registrada.');
    }

    /** API de dispositivos: POST /api/iot/evento  (header X-IOT-TOKEN). */
    public function recibirEvento(Request $request): JsonResponse
    {
        // FASE 2.F: autenticación fail-safe. Si no hay token configurado en el
        // entorno (o es el valor de ejemplo), se rechaza con 503: nunca se
        // aceptan eventos sin autenticación ni con un secreto publicado.
        $esperado = (string) config('epam.iot.token');
        $valido = $esperado !== ''
            && $esperado !== 'cambie-esto-en-su-archivo-.env'
            && hash_equals($esperado, (string) $request->header('X-IOT-TOKEN', ''));
        if (! $valido) {
            return response()->json(
                ['error' => $esperado === '' ? 'Recepción IoT no configurada en el servidor.' : 'Token IoT inválido.'],
                $esperado === '' ? 503 : 401
            );
        }

        $datos = $request->validate([
            'estacion' => 'required|string|max:60',
            'empleado_codigo' => 'nullable|string|max:30',
            'tipo' => 'required|string|max:40',
            'detectado_en' => 'nullable|date',
            'duracion_min' => 'nullable|integer|min:0|max:600',
            'observacion' => 'nullable|string|max:500',
        ]);

        $estacion = IotEstacion::where('nombre', $datos['estacion'])->first();
        // FASE 2.F: sólo estaciones activas pueden enviar eventos.
        if (! $estacion || ! $estacion->activo) {
            return response()->json(['error' => 'Estación no registrada o inactiva.'], 404);
        }
        $estacion->forceFill(['ultimo_ping' => now()])->save();

        $empleado = null;
        if (! empty($datos['empleado_codigo'])) {
            $empleado = Empleado::where('codigo', $datos['empleado_codigo'])
                ->orWhere('numero_documento', $datos['empleado_codigo'])->first();
        }

        $evento = IotEvento::create([
            'estacion_id' => $estacion->id,
            'empleado_id' => $empleado?->id,
            'tipo' => $datos['tipo'],
            'detectado_en' => $datos['detectado_en'] ?? now(),
            'duracion_min' => $datos['duracion_min'] ?? null,
            'observacion' => $datos['observacion'] ?? null,
        ]);

        return response()->json([
            'ok' => true,
            'evento_id' => $evento->id,
            'empleado_identificado' => $empleado !== null,
            'estacion' => $estacion->nombre,
        ], 201);
    }

    /** Marca un evento como Revisado o Descartado. */
    public function revisar(Request $request, IotEvento $evento)
    {
        $datos = $request->validate(['estado' => 'required|in:Revisado,Descartado']);
        $evento->update($datos);

        return redirect()->route('iot.index')->with('ok', "Evento marcado como {$datos['estado']}.");
    }
}
