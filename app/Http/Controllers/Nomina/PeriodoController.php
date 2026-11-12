<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use App\Models\Nomina\DetallePlanilla;
use App\Models\Nomina\PeriodoPlanilla;
use App\Services\PlanillaService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PeriodoController extends Controller
{
    public function __construct(private PlanillaService $planilla) {}

    public function index()
    {
        $periodos = PeriodoPlanilla::withCount('detalles')->orderByDesc('anio')->orderByDesc('mes')->paginate(15);

        return view('planilla.periodos.index', compact('periodos'));
    }

    public function create()
    {
        return view('planilla.periodos.create', [
            'periodo' => new PeriodoPlanilla([
                'anio' => now()->year, 'mes' => now()->month, 'estado' => 'Borrador',
                'periodo' => now()->locale('es')->translatedFormat('F Y'),
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'anio' => 'required|integer|min:2000|max:2100',
            'mes' => 'required|integer|min:1|max:12',
            'estado' => 'required|string|max:30',
            'observacion' => 'nullable|string|max:400',
        ]);
        $datos['periodo'] = ucfirst(now()->setDate($datos['anio'], $datos['mes'], 1)->locale('es')->translatedFormat('F Y'));

        $periodo = PeriodoPlanilla::create($datos);

        return redirect()->route('periodos.show', $periodo)->with('ok', 'Período creado. Presione «Calcular planilla» para generar los detalles.');
    }

    public function show(PeriodoPlanilla $periodo)
    {
        $periodo->load(['detalles.empleado.departamento']);

        return view('planilla.periodos.show', [
            'periodo' => $periodo,
            'totalEmpleados' => $periodo->detalles->count(),
            'promedio' => $periodo->detalles->count() ? round((float) $periodo->total_neto / $periodo->detalles->count(), 2) : 0,
        ]);
    }

    public function edit(PeriodoPlanilla $periodo)
    {
        return view('planilla.periodos.edit', compact('periodo'));
    }

    public function update(Request $request, PeriodoPlanilla $periodo)
    {
        $periodo->update($request->validate([
            'estado' => 'required|string|max:30',
            'fecha_cierre' => 'nullable|date',
            'fecha_pago' => 'nullable|date',
            'observacion' => 'nullable|string|max:400',
        ]));

        return redirect()->route('periodos.show', $periodo)->with('ok', 'Período actualizado.');
    }

    public function destroy(PeriodoPlanilla $periodo)
    {
        $periodo->delete();

        return redirect()->route('periodos.index')->with('ok', 'Período eliminado.');
    }

    /** Recalcula los detalles del período con las reglas de PlanillaService. */
    public function calcular(PeriodoPlanilla $periodo)
    {
        $this->planilla->calcularPeriodo($periodo);

        return redirect()->route('periodos.show', $periodo)
            ->with('ok', 'Planilla recalculada: '.$periodo->detalles()->count().' boletas generadas.');
    }

    /** Recibo de pago (vista imprimible) de un colaborador. */
    public function recibo(PeriodoPlanilla $periodo, DetallePlanilla $detalle)
    {
        abort_unless($detalle->periodo_planilla_id === $periodo->id, 404);
        $detalle->load('empleado.departamento', 'empleado.designacion');
        $empresa = \App\Models\Organizacion\Compania::first();

        return view('planilla.periodos.recibo', compact('periodo', 'detalle', 'empresa'));
    }

    /** Hoja A4 de la Planilla de Remuneraciones (todos los trabajadores). */
    public function planilla(PeriodoPlanilla $periodo)
    {
        $detalles = $periodo->detalles()
            ->with(['empleado.designacion', 'empleado.departamento'])->orderBy('id')->get();

        $tot = [
            'basico' => $detalles->sum(fn ($d) => (float) $d->basico),
            'asig' => $detalles->sum(fn ($d) => (float) $d->asig_familiar),
            'extras' => $detalles->sum(fn ($d) => (float) $d->horas_extras + (float) $d->bono_asistencia),
            'bruto' => $detalles->sum(fn ($d) => (float) $d->total_ingresos),
            'tardfalta' => $detalles->sum(fn ($d) => (float) $d->desc_tardanza + (float) $d->desc_ausencia),
            'pension' => $detalles->sum(fn ($d) => (float) $d->aporte_pension),
            'renta' => $detalles->sum(fn ($d) => (float) $d->renta_5ta),
            'desc' => $detalles->sum(fn ($d) => (float) $d->total_descuentos),
            'neto' => $detalles->sum(fn ($d) => (float) $d->neto),
            'essalud' => $detalles->sum(fn ($d) => (float) $d->essalud),
            'sctr' => $detalles->sum(fn ($d) => (float) $d->sctr),
            'costo' => $detalles->sum(fn ($d) => (float) $d->costo_empresa),
        ];

        return view('planilla.periodos.planilla', [
            'periodo' => $periodo, 'detalles' => $detalles, 'tot' => $tot,
            'empresa' => \App\Models\Organizacion\Compania::first(),
        ]);
    }

    /** Boleta de pago individual en PDF (plantilla peruana). */
    public function boletaPdf(PeriodoPlanilla $periodo, DetallePlanilla $detalle)
    {
        abort_unless($detalle->periodo_planilla_id === $periodo->id, 404);
        // FASE 3: no se publican boletas de períodos en borrador.
        abort_if($periodo->estado === 'Borrador', 403, 'La boleta aún no está publicada.');
        $detalle->load(['empleado.designacion', 'empleado.departamento', 'empleado.sucursal']);
        $empresa = \App\Models\Organizacion\Compania::first();
        $svc = app(\App\Services\PlanillaService::class);
        $desglose = $svc->desglosePension((float) $detalle->total_ingresos, $detalle->afp);
        $regimen = $detalle->afp === 'ONP' ? 'ONP (D.L. 19990)' : 'AFP '.$detalle->afp;

        $pdf = Pdf::loadView('planilla.periodos.pdf.boleta', [
            'periodo' => $periodo, 'detalle' => $detalle, 'empresa' => $empresa,
            'desglose' => $desglose, 'regimen' => $regimen,
        ])->setPaper('a4', 'portrait');

        $nombre = 'Boleta_'.$periodo->anio.str_pad((string) $periodo->mes, 2, '0', STR_PAD_LEFT)
            .'_'.$detalle->empleado->codigo.'.pdf';

        return $pdf->download($nombre);
    }

    /** Planilla de Remuneraciones general (todos los empleados) en PDF. */
    public function planillaPdf(PeriodoPlanilla $periodo)
    {
        $detalles = $periodo->detalles()
            ->with(['empleado.designacion', 'empleado.departamento'])->orderBy('id')->get();

        $tot = [
            'basico' => $detalles->sum(fn ($d) => (float) $d->basico),
            'asig' => $detalles->sum(fn ($d) => (float) $d->asig_familiar),
            'extras' => $detalles->sum(fn ($d) => (float) $d->horas_extras + (float) $d->bono_asistencia),
            'bruto' => $detalles->sum(fn ($d) => (float) $d->total_ingresos),
            'tardfalta' => $detalles->sum(fn ($d) => (float) $d->desc_tardanza + (float) $d->desc_ausencia),
            'pension' => $detalles->sum(fn ($d) => (float) $d->aporte_pension),
            'renta' => $detalles->sum(fn ($d) => (float) $d->renta_5ta),
            'desc' => $detalles->sum(fn ($d) => (float) $d->total_descuentos),
            'neto' => $detalles->sum(fn ($d) => (float) $d->neto),
            'essalud' => $detalles->sum(fn ($d) => (float) $d->essalud),
            'sctr' => $detalles->sum(fn ($d) => (float) $d->sctr),
            'costo' => $detalles->sum(fn ($d) => (float) $d->costo_empresa),
        ];

        $pdf = Pdf::loadView('planilla.periodos.pdf.planilla', [
            'periodo' => $periodo, 'detalles' => $detalles, 'tot' => $tot,
            'empresa' => \App\Models\Organizacion\Compania::first(),
        ])->setPaper('a4', 'portrait')->setOptions(['isRemoteEnabled' => false]);

        $nombre = 'Planilla_'.$periodo->anio.str_pad((string) $periodo->mes, 2, '0', STR_PAD_LEFT).'.pdf';

        return $pdf->download($nombre);
    }
}
