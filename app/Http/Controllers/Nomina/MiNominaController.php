<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use App\Models\Empleados\Empleado;
use App\Models\Nomina\DetallePlanilla;
use Illuminate\Http\Request;

/**
 * Módulo exclusivo del rol EMPLEADO: ve únicamente sus propias boletas,
 * autorizadas por el empleado_id vinculado a su usuario (nunca por parámetro).
 */
class MiNominaController extends Controller
{
    private function empleado(Request $request): Empleado
    {
        $empleado = Empleado::find($request->user()->empleado_id);
        abort_unless($empleado, 403, 'Su usuario no tiene un empleado vinculado.');

        return $empleado;
    }

    public function index(Request $request)
    {
        $empleado = $this->empleado($request);

        $detalles = DetallePlanilla::with(['periodo', 'empleado'])
            ->where('empleado_id', $empleado->id)
            ->orderByDesc('id')->paginate(12);

        return view('minomina.index', compact('empleado', 'detalles'));
    }

    public function boleta(Request $request, DetallePlanilla $detalle)
    {
        $empleado = $this->empleado($request);
        // Autorización en servidor: la boleta debe ser del propio empleado
        abort_unless($detalle->empleado_id === $empleado->id, 403, 'Sólo puede ver sus propias boletas.');

        $detalle->load(['empleado.designacion', 'empleado.departamento', 'empleado.sucursal', 'periodo']);
        // FASE 3: el detalle debe corresponder al período solicitado y la boleta
        // debe estar publicada (no en borrador).
        abort_unless($detalle->periodo_planilla_id === $detalle->periodo?->id, 404, 'El detalle no corresponde al período.');
        abort_if($detalle->periodo?->estado === 'Borrador', 403, 'La boleta de este período aún no está publicada.');

        $empresa = \App\Models\Organizacion\Compania::first();
        $svc = app(\App\Services\PlanillaService::class);
        $desglose = $svc->desglosePension((float) $detalle->total_ingresos, $detalle->afp);
        $regimen = $detalle->afp === 'ONP' ? 'ONP (D.L. 19990)' : 'AFP '.$detalle->afp;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('planilla.periodos.pdf.boleta', [
            'periodo' => $detalle->periodo, 'detalle' => $detalle, 'empresa' => $empresa,
            'desglose' => $desglose, 'regimen' => $regimen,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Boleta_'.$detalle->periodo->anio.str_pad((string) $detalle->periodo->mes, 2, '0', STR_PAD_LEFT).'_'.$empleado->codigo.'.pdf');
    }
}
