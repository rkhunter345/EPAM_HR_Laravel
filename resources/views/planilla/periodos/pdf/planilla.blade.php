<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Planilla de Remuneraciones · {{ $periodo->periodo }}</title>
<style>
@page{size:A4 portrait;margin:9mm}
*{box-sizing:border-box}
body{font-family:"Segoe UI",Arial,sans-serif;color:#1f272e;margin:0;font-size:8.5px}
.enc{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;border-bottom:2.5px solid #1f272e;padding-bottom:7px}
.empresa{display:flex;gap:9px;align-items:center}
.logo{width:34px;height:34px;border-radius:8px;background:#2490ef;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px}
.empresa h1{margin:0;font-size:13px;letter-spacing:.2px}
.empresa small{display:block;color:#5b6470;font-size:7.8px;margin-top:2px}
.titulo{text-align:right}
.titulo h2{margin:0;font-size:12.5px;letter-spacing:.5px}
.titulo small{display:block;color:#5b6470;font-size:7.8px;margin-top:2px}
.parametros{display:flex;gap:4px;margin:7px 0;flex-wrap:wrap}
.param{border:1px solid #dfe3e8;border-radius:4px;padding:2.5px 6px;font-size:6.8px;color:#5b6470}
.param b{display:block;font-size:8.6px;color:#1f272e}
table{width:100%;border-collapse:collapse;table-layout:fixed}
th,td{border:1px solid #c8cdd4;padding:3px 3.5px;font-size:7.2px;text-align:right;overflow:hidden}
th{background:#f0f2f5;font-weight:700;font-size:6.6px;text-transform:uppercase;text-align:center}
th.txt,td.txt{text-align:left}
tbody tr:nth-child(even) td{background:#fafbfc}
tfoot td{background:#eef0f4;font-weight:800;font-size:7.6px}
.zona{background:#f8f9fb;font-weight:700}
.pie{margin-top:7px;font-size:7px;color:#8d97a3;display:flex;justify-content:space-between;gap:10px}
.firma{margin-top:20px;display:flex;justify-content:space-around}
.firma div{text-align:center;font-size:8px;color:#5b6470}
.firma i{display:block;width:140px;border-top:1px solid #1f272e;margin-bottom:3px}
</style>
</head>
<body><div style="text-align:center;margin-bottom:8px;"><img src="{{ public_path('img/logo-epam.png') }}" style="height:52px;"></div>
<div class="enc">
    <div class="empresa">
        <span class="logo">E</span>
        <div>
            <h1>{{ mb_strtoupper($empresa->nombre ?? 'MULTISERVICIOS EPAM E.I.R.L.') }}</h1>
            <small>RUC: {{ $empresa->ruc ?? '—' }}{{ $empresa->direccion ? ' · '.mb_strtoupper($empresa->direccion) : '' }}</small>
        </div>
    </div>
    <div class="titulo">
        <h2>PLANILLA DE REMUNERACIONES</h2>
        <small>Período: <strong>{{ $periodo->periodo }}</strong> · Estado: {{ $periodo->estado }}</small>
        <small>Generada: {{ now()->format('d/m/Y H:i') }} · Sistema EPAM HR</small>
    </div>
</div>

<div class="parametros">
    <span class="param">RMV<b>S/ 1,230.00</b></span>
    <span class="param">UIT 2026<b>S/ 5,500.00</b></span>
    <span class="param">ONP<b>13 %</b></span>
    <span class="param">AFP<b>10 % + comisión + prima 1.37 %</b></span>
    <span class="param">Asig. fam.<b>10 % RMV (S/ 123.00)</b></span>
    <span class="param">H. extras<b>+25 % / +35 %</b></span>
    <span class="param">EsSalud<b>9 %</b></span>
    <span class="param">SCTR<b>1.25 %</b></span>
</div>

<table>
    <thead>
        <tr>
            <th rowspan="2" style="width:4%">N°</th>
            <th rowspan="2" style="width:8%">DNI</th>
            <th rowspan="2" class="txt" style="width:16%">Apellidos y nombres</th>
            <th rowspan="2" class="txt" style="width:10%">Cargo</th>
            <th rowspan="2" style="width:6%">Sist. pens.</th>
            <th colspan="4" class="zona">Ingresos del trabajador</th>
            <th colspan="4" class="zona">Retenciones del trabajador</th>
            <th rowspan="2" style="width:7.5%">Neto a pagar</th>
            <th colspan="3" class="zona">Aportes del empleador</th>
        </tr>
        <tr>
            <th style="width:7.5%">Básico</th>
            <th style="width:6%">Asig. fam.</th>
            <th style="width:6%">Otros</th>
            <th style="width:7.5%">Total bruto</th>
            <th style="width:6%">Tard./falta</th>
            <th style="width:6.5%">Pensión</th>
            <th style="width:5.5%">Renta 5.ª</th>
            <th style="width:7.5%">Total desc.</th>
            <th style="width:6%">EsSalud</th>
            <th style="width:5%">SCTR</th>
            <th style="width:7.5%">Costo empr.</th>
        </tr>
    </thead>
    <tbody>
    @forelse($detalles as $i => $d)
        <tr>
            <td>{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</td>
            <td>{{ $d->empleado->numero_documento ?: $d->empleado->codigo }}</td>
            <td class="txt">{{ mb_strtoupper($d->empleado->apellido).' '.mb_strtoupper($d->empleado->primer_nombre.' '.($d->empleado->segundo_nombre ?? '')) }}</td>
            <td class="txt">{{ mb_strtoupper($d->empleado->designacion->nombre ?? '—') }}</td>
            <td>{{ $d->afp }}</td>
            <td>{{ number_format((float) $d->basico, 2) }}</td>
            <td>{{ number_format((float) $d->asig_familiar, 2) }}</td>
            <td>{{ number_format((float) $d->horas_extras + (float) $d->bono_asistencia, 2) }}</td>
            <td><strong>{{ number_format((float) $d->total_ingresos, 2) }}</strong></td>
            <td>{{ number_format((float) $d->desc_tardanza + (float) $d->desc_ausencia, 2) }}</td>
            <td>{{ number_format((float) $d->aporte_pension, 2) }}</td>
            <td>{{ number_format((float) $d->renta_5ta, 2) }}</td>
            <td><strong>{{ number_format((float) $d->total_descuentos, 2) }}</strong></td>
            <td><strong>{{ number_format((float) $d->neto, 2) }}</strong></td>
            <td>{{ number_format((float) $d->essalud, 2) }}</td>
            <td>{{ number_format((float) $d->sctr, 2) }}</td>
            <td>{{ number_format((float) $d->costo_empresa, 2) }}</td>
        </tr>
    @empty
        <tr><td colspan="17" style="text-align:center;padding:12px">Período sin boletas.</td></tr>
    @endforelse
    </tbody>
    @if(count($detalles))
    <tfoot>
        <tr>
            <td colspan="5" class="txt">TOTALES S/ · {{ count($detalles) }} trabajadores</td>
            <td>{{ number_format($tot['basico'], 2) }}</td>
            <td>{{ number_format($tot['asig'], 2) }}</td>
            <td>{{ number_format($tot['extras'], 2) }}</td>
            <td>{{ number_format($tot['bruto'], 2) }}</td>
            <td>{{ number_format($tot['tardfalta'], 2) }}</td>
            <td>{{ number_format($tot['pension'], 2) }}</td>
            <td>{{ number_format($tot['renta'], 2) }}</td>
            <td>{{ number_format($tot['desc'], 2) }}</td>
            <td>{{ number_format($tot['neto'], 2) }}</td>
            <td>{{ number_format($tot['essalud'], 2) }}</td>
            <td>{{ number_format($tot['sctr'], 2) }}</td>
            <td>{{ number_format($tot['costo'], 2) }}</td>
        </tr>
    </tfoot>
    @endif
</table>

<div class="pie">
    <span>Neto = total bruto − descuentos (pensión + renta 5.ª + tardanzas/faltas). EsSalud y SCTR son aportes de la empresa.</span>
    <span>Declaración mensual: T-Registro / PLAME (SUNAT).</span>
</div>
<div class="firma">
    <div><i></i>Elaborado por (RR.HH.)</div>
    <div><i></i>Revisado por (Administrador)</div>
</div>
</body>
</html>
