<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;

use App\Models\Asistencias\Asistencia;
use App\Models\Nomina\DetallePlanilla;
use App\Models\Empleados\Empleado;
use App\Models\Nomina\PeriodoPlanilla;
use App\Models\Sistema\ReporteGenerado;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    public function index()
    {
        return view('reportes.index', [
            'tipos' => ReporteGenerado::TIPOS,
            'empleados' => Empleado::orderBy('nombre_completo')->pluck('nombre_completo', 'id'),
            'periodos' => PeriodoPlanilla::orderByDesc('anio')->orderByDesc('mes')->pluck('periodo', 'id'),
            'recientes' => ReporteGenerado::with('empleado')->orderByDesc('id')->limit(10)->get(),
        ]);
    }

    public function generar(Request $request)
    {
        $datos = $request->validate([
            'tipo' => 'required|string|max:60',
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
            'empleado_id' => 'nullable|exists:empleados,id',
            'periodo_id' => 'nullable|exists:periodos_planilla,id',
        ]);

        $reporte = ReporteGenerado::create([
            'tipo' => $datos['tipo'],
            'periodo' => ! empty($datos['desde'])
                ? $datos['desde'].' a '.($datos['hasta'] ?? now()->toDateString())
                : null,
            'empleado_id' => $datos['empleado_id'] ?? null,
            'generado_por' => $request->user()->id,
            'fecha' => now()->toDateString(),
            'detalle' => 'Generado desde el módulo de reportes.',
        ]);

        return redirect()->route('reportes.show', $reporte);
    }

    /** Fechas efectivas: primero las de la URL, luego las guardadas del reporte. */
    private function rango(Request $request, ReporteGenerado $reporte): array
    {
        $guardado = $reporte->periodo ? explode(' a ', $reporte->periodo) : [];
        $desde = $request->query('desde') ?: ($guardado[0] ?? now()->startOfMonth()->toDateString());
        $hasta = $request->query('hasta') ?: ($guardado[1] ?? now()->toDateString());

        // Normaliza d/m/Y -> Y-m-d (acepta ambos formatos)
        $normalizar = function (string $v): string {
            if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', trim($v), $m)) {
                return "{$m[3]}-{$m[2]}-{$m[1]}";
            }
            return trim($v);
        };

        return [$normalizar($desde), $normalizar($hasta)];
    }

    public function show(Request $request, ReporteGenerado $reporte)
    {
        [$desde, $hasta] = $this->rango($request, $reporte);

        $asistencias = Asistencia::with('empleado')
            ->when($reporte->empleado_id, fn ($q) => $q->where('empleado_id', $reporte->empleado_id))
            ->whereDate('fecha', '>=', $desde)
            ->whereDate('fecha', '<=', $hasta)
            ->orderByDesc('fecha')->limit(200)->get();

        $planilla = DetallePlanilla::with(['empleado', 'periodo'])
            ->when($reporte->empleado_id, fn ($q) => $q->where('empleado_id', $reporte->empleado_id))
            ->orderByDesc('id')->limit(200)->get();

        return view('reportes.show', compact('reporte', 'asistencias', 'planilla', 'desde', 'hasta'));
    }

    /** Otras descargas con el MISMO filtro: csv (Excel), A4 horizontal (imprimir). */
    public function exportar(Request $request, ReporteGenerado $reporte, string $formato)
    {
        [$desde, $hasta] = $this->rango($request, $reporte);

        $filas = Asistencia::with('empleado')
            ->when($reporte->empleado_id, fn ($q) => $q->where('empleado_id', $reporte->empleado_id))
            ->whereDate('fecha', '>=', $desde)
            ->whereDate('fecha', '<=', $hasta)
            ->orderByDesc('fecha')->limit(5000)->get();

        if ($formato === 'csv') {
            $nombre = 'Reporte_'.preg_replace('/[^A-Za-z0-9]/', '_', $reporte->tipo).'_'.$desde.'_a_'.$hasta.'.csv';

            return response()->streamDownload(function () use ($filas) {
                $s = fopen('php://output', 'w');
                fputs($s, "\xEF\xBB\xBF"); // BOM para Excel
                fputcsv($s, ['Empleado', 'Código', 'Fecha', 'Entrada', 'Salida', 'Tardanza (min)', 'Minutos trabajados', 'Estado'], ';');
                foreach ($filas as $a) {
                    fputcsv($s, [
                        $a->empleado->nombre_completo ?? '—', $a->empleado->codigo ?? '—',
                        $a->fecha->format('d/m/Y'), $a->hora_entrada ?? '', $a->hora_salida ?? '',
                        $a->tardanza_min, $a->minutos_trabajados, $a->estado,
                    ], ';');
                }
                fclose($s);
            }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        if ($formato === 'horizontal') {
            return view('reportes.horizontal', compact('reporte', 'filas', 'desde', 'hasta'));
        }

        abort(404);
    }
}
