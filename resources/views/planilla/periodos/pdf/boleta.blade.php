<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Boleta de Pago · {{ $detalle->empleado->nombre_completo }}</title>
<style>
@page{margin:12mm 14mm}
*{box-sizing:border-box}
body{font-family:"Segoe UI",Arial,sans-serif;color:#1f272e;margin:0;font-size:10.5px}
.marco{border:1.5px solid #1f272e;padding:0}
.marco-hd{display:flex;justify-content:space-between;border-bottom:1.5px solid #1f272e}
.empresa{padding:9px 12px}
.empresa h1{margin:0;font-size:13.5px;letter-spacing:.2px}
.empresa small{display:block;color:#444;margin-top:2px}
.titulo-boleta{padding:9px 12px;border-left:1.5px solid #1f272e;text-align:right;background:#f0f2f5;min-width:190px}
.titulo-boleta h2{margin:0;font-size:12.5px;letter-spacing:.4px}
.titulo-boleta small{display:block;margin-top:2px}
.datos{display:flex;flex-wrap:wrap;padding:0}
.fila{width:100%;display:flex;border-bottom:.5px solid #9aa2ac}
.fila:last-child{border-bottom:none}
.campo{width:50%;display:flex}
.campo .etq{width:52%;padding:4.5px 10px;color:#333;font-weight:600;border-right:.5px solid #9aa2ac}
.campo .val{width:48%;padding:4.5px 10px;border-right:.5px solid #9aa2ac}
.secciones{display:flex}
.sec{flex:1;padding:7px 10px 9px}
.sec.izq{border-right:1px solid #1f272e}
.sec h3{margin:0 0 5px;font-size:9.5px;letter-spacing:.4px;text-transform:uppercase;border-bottom:1px solid #1f272e;padding-bottom:3px}
.concepto{display:flex;justify-content:space-between;padding:2.2px 0}
.concepto span:last-child{font-variant-numeric:tabular-nums}
.tot-fila{display:flex;justify-content:space-between;border-top:1px solid #1f272e;margin-top:5px;padding-top:4px;font-weight:800}
.red{color:#a33027}
.verde{color:#11663a}
.neto{background:#eaf4fe;padding:8px 12px;display:flex;justify-content:space-between;align-items:center;border-top:1.5px solid #1f272e}
.neto strong{font-size:15px;letter-spacing:-.3px}
.neto .etq{font-weight:700;text-transform:uppercase;letter-spacing:.5px;font-size:11px}
.pie{margin-top:8px;display:flex;justify-content:space-between;font-size:8.5px;color:#555}
.pie .firmas{margin-top:26px;display:flex;gap:60px}
.pie .firmas div{text-align:center;font-size:9px;color:#333}
.pie .firmas i{display:block;width:150px;border-top:1px solid #1f272e;margin-bottom:3px}
</style>
</head>
<body><div style="text-align:center;margin-bottom:8px;"><img src="{{ public_path('img/logo-epam.png') }}" style="height:52px;"></div>
@php $d = $detalle; $e = $d->empleado; $extras = (float) $d->horas_extras; $otros = (float) $d->bono_asistencia; @endphp
<div class="marco">
    <div class="marco-hd">
        <div class="empresa">
            <h1>{{ mb_strtoupper($empresa->nombre ?? 'MULTISERVICIOS EPAM E.I.R.L.') }}</h1>
            <small>RUC: {{ $empresa->ruc ?? '—' }}</small>
            <small>{{ $empresa->direccion ?? '' }}</small>
        </div>
        <div class="titulo-boleta">
            <h2>BOLETA DE PAGO</h2>
            <small><strong>MES:</strong> {{ $periodo->periodo }}</small>
            <small><strong>Estado:</strong> {{ $periodo->estado }}</small>
        </div>
    </div>

    <div class="datos">
        <div class="fila">
            <div class="campo"><span class="etq">Código:</span><span class="val">{{ $e->codigo }}</span>
                <span class="etq">Fecha de ingreso:</span><span class="val">{{ $e->fecha_ingreso?->format('d/m/Y') ?? '—' }}</span></div>
        </div>
        <div class="fila">
            <div class="campo"><span class="etq">Trabajador:</span><span class="val">{{ mb_strtoupper($e->nombre_completo) }}</span>
                <span class="etq">Tipo trabajador:</span><span class="val">{{ $e->tipo_empleo->nombre ?? 'Empleado privado' }}</span></div>
        </div>
        <div class="fila">
            <div class="campo"><span class="etq">Documento (DNI):</span><span class="val">{{ $e->numero_documento ?? '—' }}</span>
                <span class="etq">Régimen laboral:</span><span class="val">{{ $e->tipo_contrato->nombre ?? 'D.L. 728 (Act. privada)' }}</span></div>
        </div>
        <div class="fila">
            <div class="campo"><span class="etq">Cargo:</span><span class="val">{{ $e->designacion->nombre ?? '—' }}</span>
                <span class="etq">Régimen pensionario:</span><span class="val">{{ $regimen }}</span></div>
        </div>
        <div class="fila">
            <div class="campo"><span class="etq">Área:</span><span class="val">{{ $e->departamento->nombre ?? '—' }}</span>
                <span class="etq">Sucursal:</span><span class="val">{{ $e->sucursal->nombre ?? '—' }}</span></div>
        </div>
        <div class="fila">
            <div class="campo"><span class="etq">Días laborados:</span><span class="val">{{ 30 - $d->ausencias }}</span>
                <span class="etq">Tardanzas:</span><span class="val">{{ $d->tardanzas_min }} min</span></div>
        </div>
    </div>

    <div class="secciones">
        <div class="sec izq">
            <h3>Ingresos (conceptos remunerativos)</h3>
            <div class="concepto"><span>Sueldo o remuneración básica</span><span>S/ {{ number_format((float) $d->basico, 2) }}</span></div>
            <div class="concepto"><span>Asignación familiar (si corresponde)</span><span>S/ {{ number_format((float) $d->asig_familiar, 2) }}</span></div>
            <div class="concepto"><span>Horas extras (25 % / 35 %)</span><span>S/ {{ number_format($extras, 2) }}</span></div>
            <div class="concepto"><span>Bonificación / bono de asistencia</span><span>S/ {{ number_format($otros, 2) }}</span></div>
            <div class="tot-fila"><span>TOTAL INGRESOS</span><span>S/ {{ number_format((float) $d->total_ingresos, 2) }}</span></div>
        </div>
        <div class="sec">
            <h3>Descuentos / aportes del trabajador</h3>
            <div class="concepto"><span>{{ $d->afp === 'ONP' ? 'Fondo de pensiones (ONP 13 %)' : 'Fondo de pensiones (AFP '.$d->afp.')' }}</span><span class="red">S/ {{ number_format($desglose['fondo'], 2) }}</span></div>
            @if($d->afp !== 'ONP')
                <div class="concepto"><span>Comisión sobre flujo AFP</span><span class="red">S/ {{ number_format($desglose['comision'], 2) }}</span></div>
                <div class="concepto"><span>Prima de seguro AFP</span><span class="red">S/ {{ number_format($desglose['prima'], 2) }}</span></div>
            @endif
            <div class="concepto"><span>Impuesto a la quinta categoría</span><span class="red">S/ {{ number_format((float) $d->renta_5ta, 2) }}</span></div>
            <div class="concepto"><span>Tardanzas</span><span class="red">S/ {{ number_format((float) $d->desc_tardanza, 2) }}</span></div>
            <div class="concepto"><span>Inasistencias</span><span class="red">S/ {{ number_format((float) $d->desc_ausencia, 2) }}</span></div>
            <div class="concepto"><span>Adelantos</span><span class="red">S/ {{ number_format((float) $d->adelantos, 2) }}</span></div>
            <div class="tot-fila"><span>TOTAL DESCUENTOS</span><span class="red">S/ {{ number_format((float) $d->total_descuentos, 2) }}</span></div>
        </div>
    </div>

    <div class="sec" style="border-top:1px solid #1f272e">
        <h3>Aportaciones del empleador (no descuentos al trabajador)</h3>
        <div class="concepto"><span>EsSalud (9 %)</span><span>S/ {{ number_format((float) $d->essalud, 2) }}</span></div>
        <div class="concepto"><span>SCTR (1.25 %)</span><span>S/ {{ number_format((float) $d->sctr, 2) }}</span></div>
        <div class="concepto"><span><strong>Costo total para la empresa</strong></span><span><strong>S/ {{ number_format((float) $d->costo_empresa, 2) }}</strong></span></div>
    </div>

    <div class="neto">
        <span class="etq">Neto a pagar</span>
        <strong>S/ {{ number_format((float) $d->neto, 2) }}</strong>
    </div>
</div>

<div class="pie">
    <div>
        Documento generado por el sistema EPAM HR el {{ now()->format('d/m/Y H:i') }}.
        <br>La planilla electrónica oficial se declara en T-Registro / PLAME (SUNAT).
    </div>
    <div class="firmas">
        <div><i></i>Entregado conforme<br><small>Trabajador</small></div>
        <div><i></i>{{ mb_strtoupper($empresa->nombre ?? 'Representante legal') }}<br><small>RR.HH. / Administrador</small></div>
    </div>
</div>
</body>
</html>
