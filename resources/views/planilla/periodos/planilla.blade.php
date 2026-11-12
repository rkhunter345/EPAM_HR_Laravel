<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Planilla de Remuneraciones · {{ $periodo->periodo }}</title>
<style>
@page { size: A4 portrait; margin: 10mm; }
* { box-sizing: border-box; }
body { font-family: "Segoe UI", Arial, sans-serif; color: #1f272e; margin: 0; padding: 12px; background: #eef0f4; font-size: 10px; }
.btns { max-width: 190mm; margin: 0 auto 10px; display: flex; gap: 8px; justify-content: flex-end; }
.btn { font: inherit; font-size: 11.5px; padding: 7px 16px; border-radius: 7px; border: 1px solid #c8cdd4; background: #fff; cursor: pointer; }
.btn-pri { background: #2490ef; border-color: #2490ef; color: #fff; }
.hoja { width: 190mm; min-height: 271mm; margin: 0 auto; background: #fff; padding: 10mm 9mm; box-shadow: 0 2px 10px rgba(16,24,40,.08); }
.enc { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; border-bottom: 2.5px solid #1f272e; padding-bottom: 8px; }
.enc .empresa { display: flex; gap: 10px; align-items: center; }
.enc .logo { width: 40px; height: 40px; border-radius: 9px; background: #2490ef; color: #fff; display: grid; place-items: center; font-weight: 800; font-size: 15px; }
.enc h1 { margin: 0; font-size: 15px; letter-spacing: .2px; }
.enc .empresa small { display: block; color: #5b6470; font-size: 8.5px; margin-top: 2px; }
.titulo { text-align: right; }
.titulo h2 { margin: 0; font-size: 13.5px; letter-spacing: .6px; }
.titulo small { display: block; color: #5b6470; font-size: 8.5px; margin-top: 2px; }
.parametros { display: flex; gap: 5px; margin: 8px 0; flex-wrap: wrap; }
.param { border: 1px solid #dfe3e8; border-radius: 5px; padding: 3px 8px; font-size: 7.6px; color: #5b6470; }
.param b { display: block; font-size: 10px; color: #1f272e; }
table { width: 100%; border-collapse: collapse; table-layout: fixed; }
th, td { border: 1px solid #c8cdd4; padding: 3.5px 4px; font-size: 8px; text-align: right; overflow: hidden; }
th { background: #f0f2f5; font-weight: 700; font-size: 7.3px; text-transform: uppercase; text-align: center; letter-spacing: .2px; }
thead tr:first-child th { border-bottom: none; }
th.txt, td.txt { text-align: left; }
tbody tr:nth-child(even) td { background: #fafbfc; }
tfoot td { background: #eef0f4; font-weight: 800; font-size: 8.4px; }
.num { font-variant-numeric: tabular-nums; }
.zona { background: #f8f9fb; font-weight: 700; }
.pie { margin-top: 8px; font-size: 7.6px; color: #8d97a3; display: flex; justify-content: space-between; gap: 12px; }
.firma { margin-top: 22px; display: flex; justify-content: space-around; }
.firma div { text-align: center; font-size: 8.5px; color: #5b6470; }
.firma i { display: block; width: 150px; border-top: 1px solid #1f272e; margin-bottom: 4px; }
@media print { body { background: #fff; padding: 0; } .hoja { box-shadow: none; width: auto; min-height: auto; } .btns { display: none; } }
</style>
</head>
<body><div style="text-align:center;margin-bottom:8px;"><img src="{{ public_path('img/logo-epam.png') }}" style="height:52px;"></div>
<div class="btns">
    <a class="btn" href="{{ route('periodos.show', $periodo) }}">Volver</a>
    <button class="btn btn-pri" onclick="window.print()">Imprimir / Guardar PDF (A4)</button>
</div>

<div class="hoja">
    <div class="enc">
        <div class="empresa">
            <span class="logo">E</span>
            <div>
                <h1>{{ $empresa->nombre ?? 'MULTISERVICIOS EPAM E.I.R.L.' }}</h1>
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
        <span class="param">Asig. familiar<b>10 % RMV (S/ 123.00)</b></span>
        <span class="param">Horas extras<b>+25 % / +35 %</b></span>
        <span class="param">EsSalud<b>9 %</b></span>
        <span class="param">SCTR<b>1.25 %</b></span>
        <span class="param">Renta 5.ª<b>7 UIT exoneradas + tramos</b></span>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width:5%">N°</th>
                <th rowspan="2" style="width:8.5%">Código</th>
                <th rowspan="2" class="txt" style="width:16%">Apellidos y nombres</th>
                <th rowspan="2" class="txt" style="width:11%">Cargo u ocupación</th>
                <th rowspan="2" style="width:6%">Sist. pens.</th>
                <th colspan="4" class="zona">Ingresos del trabajador</th>
                <th colspan="4" class="zona">Retenciones a cargo del trabajador</th>
                <th rowspan="2" style="width:8%">Neto a pagar</th>
                <th colspan="3" class="zona">Aportes del empleador</th>
            </tr>
            <tr>
                <th style="width:8%">Sueldo básico</th>
                <th style="width:6%">Asig. fam.</th>
                <th style="width:6.5%">Otros</th>
                <th style="width:8%">Total bruto</th>
                <th style="width:6.5%">Tard./falta</th>
                <th style="width:6.5%">Pensión</th>
                <th style="width:6%">Renta 5.ª</th>
                <th style="width:8%">Total desc.</th>
                <th style="width:6.5%">EsSalud</th>
                <th style="width:5.5%">SCTR</th>
                <th style="width:8.5%">Costo empresa</th>
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
                <td class="num">{{ number_format((float) $d->basico, 2) }}</td>
                <td class="num">{{ number_format((float) $d->asig_familiar, 2) }}</td>
                <td class="num">{{ number_format((float) $d->horas_extras + (float) $d->bono_asistencia, 2) }}</td>
                <td class="num"><strong>{{ number_format((float) $d->total_ingresos, 2) }}</strong></td>
                <td class="num">{{ number_format((float) $d->desc_tardanza + (float) $d->desc_ausencia, 2) }}</td>
                <td class="num">{{ number_format((float) $d->aporte_pension, 2) }}</td>
                <td class="num">{{ number_format((float) $d->renta_5ta, 2) }}</td>
                <td class="num"><strong>{{ number_format((float) $d->total_descuentos, 2) }}</strong></td>
                <td class="num"><strong>{{ number_format((float) $d->neto, 2) }}</strong></td>
                <td class="num">{{ number_format((float) $d->essalud, 2) }}</td>
                <td class="num">{{ number_format((float) $d->sctr, 2) }}</td>
                <td class="num">{{ number_format((float) $d->costo_empresa, 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="17" style="text-align:center;padding:14px">Período sin boletas. Regrese y presione «Calcular planilla».</td></tr>
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
        <span>Sueldo neto = total bruto − descuentos (pensión + renta 5.ª + tardanzas/faltas). EsSalud y SCTR son aportes de la empresa, no descuentos al trabajador.</span>
        <span>Declaración mensual: T-Registro / PLAME (SUNAT) · Boletas de pago entregadas a cada trabajador.</span>
    </div>
    <div class="firma">
        <div><i></i>Elaborado por (RR.HH.)</div>
        <div><i></i>Revisado por (Administrador)</div>
    </div>
</div>
</body>
</html>
