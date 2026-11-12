<?php

namespace App\Services;

use App\Models\Asistencias\Asistencia;
use App\Models\Nomina\DetallePlanilla;
use App\Models\Empleados\Empleado;
use App\Models\Nomina\PeriodoPlanilla;

/**
 * Motor de Planilla de Remuneraciones — Perú 2026.
 *
 * 1) Ingresos:   básico (≥ RMV) + asignación familiar (10% RMV) + horas
 *    extras (primeras 2 h +25%, desde la 3.ª +35%) + otros + bono asistencia.
 * 2) Descuentos: tardanzas y faltas + ONP 13% o AFP (10% + comisión flujo
 *    + prima 1.37%) + renta de 5.ª (7 UIT exoneradas + tramos).
 * 3) Aportes del empleador: EsSalud 9% y SCTR 1.25%.
 * 4) Costo empresa = bruto + aportes del empleador.
 */
class PlanillaService
{
    public const JORNADA_MIN = 480;

    public function calcularPeriodo(PeriodoPlanilla $periodo): PeriodoPlanilla
    {
        $inicio = now()->setDate($periodo->anio, $periodo->mes, 1)->startOfMonth();
        $fin = $inicio->copy()->endOfMonth();

        $periodo->detalles()->delete();

        $totIngresos = $totDescuentos = $totNeto = $totCosto = 0.0;

        foreach (Empleado::query()->where('estado', 'Activo')->with('calendario')->get() as $empleado) {
            $detalle = $this->calcularEmpleado($empleado, $inicio->toDateString(), $fin->toDateString());
            $detalle->periodo_planilla_id = $periodo->id;
            $detalle->save();

            $totIngresos += (float) $detalle->total_ingresos;
            $totDescuentos += (float) $detalle->total_descuentos;
            $totNeto += (float) $detalle->neto;
            $totCosto += (float) $detalle->costo_empresa;
        }

        $periodo->fill([
            'total_ingresos' => round($totIngresos, 2),
            'total_descuentos' => round($totDescuentos, 2),
            'total_neto' => round($totNeto, 2),
            'estado' => $periodo->estado === 'Borrador' ? 'Revisión' : $periodo->estado,
        ])->save();

        return $periodo;
    }

    public function calcularEmpleado(Empleado $empleado, string $desde, string $hasta): DetallePlanilla
    {
        $cfg = config('planilla');
        $rmv = (float) $cfg['rmv'];

        // ---- Ingresos -----------------------------------------------------
        $basico = max((float) ($empleado->salario_mensual ?: 0), 0.0);
        if ($basico > 0 && $basico < $rmv) {
            $basico = $rmv; // el básico no puede ser menor a la RMV
        }
        $valorDia = $basico / 30;
        $valorMinuto = $basico / (30 * self::JORNADA_MIN);
        $valorHora = $valorMinuto * 60;

        $asigFamiliar = $empleado->asignacion_familiar
            ? round($rmv * $cfg['asignacion_familiar_pct'], 2) : 0.0;

        $registros = Asistencia::query()
            ->where('empleado_id', $empleado->id)
            ->whereBetween('fecha', [$desde, $hasta])->get();

        $tardanzasMin = (int) $registros->sum('tardanza_min');
        $ausencias = $registros->where('estado', 'Ausente')->count();

        $extrasMin = (int) $registros->sum(fn ($r) => max(0, (int) $r->minutos_trabajados - self::JORNADA_MIN));
        $minT1 = min($extrasMin, (int) $cfg['min_extras_tasa_1']);
        $minT2 = max(0, $extrasMin - $minT1);
        $horasExtras = round(($minT1 / 60) * $valorHora * $cfg['recargo_hora_extra_1']
            + ($minT2 / 60) * $valorHora * $cfg['recargo_hora_extra_2'], 2);

        $bonoAsistencia = $ausencias === 0 && $tardanzasMin === 0 ? (float) $cfg['bono_asistencia'] : 0.0;

        $bruto = round($basico + $asigFamiliar + $horasExtras + $bonoAsistencia, 2);

        // ---- Descuentos del trabajador ------------------------------------
        $descTardanza = round($tardanzasMin * $valorMinuto, 2);
        $descAusencia = round($ausencias * $valorDia, 2);

        $afp = $empleado->afp ?: 'ONP';
        $desglose = $this->desglosePension($bruto, $afp);
        $pension = $desglose['total'];

        $renta = $this->rentaQuintaMensual($bruto);

        $descuentos = round($descTardanza + $descAusencia + $pension + $renta, 2);

        // ---- Aportes del empleador ----------------------------------------
        $essalud = round(($basico + $asigFamiliar) * $cfg['essalud'], 2);
        $sctr = round($bruto * $cfg['sctr'], 2);
        $costoEmpresa = round($bruto + $essalud + $sctr, 2);

        return new DetallePlanilla([
            'empleado_id' => $empleado->id,
            'basico' => round($basico, 2),
            'asig_familiar' => $asigFamiliar,
            'horas_extras' => $horasExtras,
            'bono_asistencia' => $bonoAsistencia,
            'total_ingresos' => $bruto,
            'desc_tardanza' => $descTardanza,
            'desc_ausencia' => $descAusencia,
            'aporte_pension' => $pension,
            'afp' => $afp,
            'renta_5ta' => $renta,
            'adelantos' => 0,
            'total_descuentos' => $descuentos,
            'neto' => round($bruto - $descuentos, 2),
            'tardanzas_min' => $tardanzasMin,
            'ausencias' => $ausencias,
            'essalud' => $essalud,
            'sctr' => $sctr,
            'costo_empresa' => $costoEmpresa,
        ]);
    }

    /** Desglose del aporte pensionario del trabajador (boleta de pago). */
    public function desglosePension(float $bruto, string $afp): array
    {
        $cfg = config('planilla');
        if ($afp === 'ONP') {
            $fondo = round($bruto * $cfg['onp'], 2);
            return ['fondo' => $fondo, 'comision' => 0.0, 'prima' => 0.0, 'total' => $fondo];
        }
        $fondo = round($bruto * $cfg['afp_aporte'], 2);
        $comision = round($bruto * (float) ($cfg['afp_comisiones'][$afp] ?? 0.0), 2);
        $prima = round($bruto * $cfg['afp_prima_seguro'], 2);
        return ['fondo' => $fondo, 'comision' => $comision, 'prima' => $prima,
            'total' => round($fondo + $comision + $prima, 2)];
    }

    /** Retención mensual de renta de 5.ª: 7 UIT exoneradas + tramos anuales / 12. */
    public function rentaQuintaMensual(float $remuneracionMensual): float
    {
        $cfg = config('planilla');
        $uit = (float) $cfg['uit'];
        $base = max(0.0, $remuneracionMensual * 12 - $cfg['renta_uit_exoneradas'] * $uit);
        if ($base <= 0.0) {
            return 0.0;
        }

        $impuesto = 0.0;
        $previo = 0.0;
        foreach ($cfg['renta_tramos_uit'] as $hastaUit => $tasa) {
            if ($base <= $previo) break;
            $tope = min($base, $hastaUit * $uit);
            $impuesto += ($tope - $previo) * $tasa;
            $previo = $hastaUit * $uit;
        }
        if ($base > $previo) {
            $impuesto += ($base - $previo) * $cfg['renta_tasa_exceso'];
        }

        return round($impuesto / 12, 2);
    }
}
