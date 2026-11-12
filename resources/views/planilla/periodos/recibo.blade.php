<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recibo de pago · {{ $periodo->periodo }}</title>
    <style>
        body{font-family:"Segoe UI",Arial,sans-serif;color:#1f272e;margin:0;padding:24px;font-size:12px}
        .recibo{max-width:720px;margin:0 auto;border:1px solid #e6e8ec;border-radius:10px;overflow:hidden}
        .cab{background:#2490ef;color:#fff;padding:14px 18px}
        texture .cab h1{margin:0;font-size:15px}
        .cab small{opacity:.9}
        .cuerpo{padding:18px}
        table{width:100%;border-collapse:collapse;margin-top:10px}
        th,td{text-align:left;padding:6px 8px;border-bottom:1px solid #eef0f4;font-size:12px}
        th{color:#8d97a3;font-weight:600;font-size:11px}
        .totales{display:flex;gap:14px;margin-top:14px}
        .total{flex:1;border:1px solid #e6e8ec;border-radius:8px;padding:10px}
        .total span{display:block;color:#8d97a3;font-size:11px}
        .total strong{font-size:15px}
        .neto{background:#eaf4fe;border-color:#cfe6fb}
        .pie{margin-top:16px;font-size:10.5px;color:#8d97a3}
        @media print{body{padding:0}.recibo{border:0}}
    </style>
</head>
<body onload="window.print()"><div style="text-align:center;margin-bottom:8px;"><img src="{{ public_path('img/logo-epam.png') }}" style="height:52px;"></div>
<div class="recibo">
    <div class="cab">
        <h1>{{ $empresa->nombre ?? 'MULTISERVICIOS EPAM E.I.R.L.' }}</h1>
        <small>Recibo de remuneraciones · {{ $periodo->periodo }} · RUC {{ $empresa->ruc ?? '—' }}</small>
    </div>
    <div class="cuerpo">
        <table>
            <tr><th>Empleado</th><td>{{ $detalle->empleado->nombre_completo }}</td><th>Código</th><td>{{ $detalle->empleado->codigo }}</td></tr>
            <tr><th>Departamento</th><td>{{ $detalle->empleado->departamento->nombre ?? '—' }}</td><th>Designación</th><td>{{ $detalle->empleado->designacion->nombre ?? '—' }}</td></tr>
            <tr><th>Documento</th><td>{{ $detalle->empleado->tipo_documento }} {{ $detalle->empleado->numero_documento }}</td><th>Fecha de pago</th><td>{{ optional($periodo->fecha_pago)->format('d/m/Y') ?? '—' }}</td></tr>
        </table>

        <table>
            <thead><tr><th>Ingresos</th><th style="text-align:right">Monto</th><th>Descuentos</th><th style="text-align:right">Monto</th></tr></thead>
            <tbody>
                <tr><td>Remuneración básica</td><td style="text-align:right">S/ {{ number_format((float) $detalle->basico, 2) }}</td>
                    <td>Descuento por tardanza ({{ $detalle->tardanzas_min }} min)</td><td style="text-align:right">S/ {{ number_format((float) $detalle->desc_tardanza, 2) }}</td></tr>
                <tr><td>Horas extras</td><td style="text-align:right">S/ {{ number_format((float) $detalle->horas_extras, 2) }}</td>
                    <td>Descuento por falta ({{ $detalle->ausencias }} días)</td><td style="text-align:right">S/ {{ number_format((float) $detalle->desc_ausencia, 2) }}</td></tr>
                <tr><td>Bono de asistencia</td><td style="text-align:right">S/ {{ number_format((float) $detalle->bono_asistencia, 2) }}</td>
                    <td>Aporte pensionario</td><td style="text-align:right">S/ {{ number_format((float) $detalle->aporte_pension, 2) }}</td></tr>
                <tr><td>—</td><td></td><td>Renta de 5ta categoría</td><td style="text-align:right">S/ {{ number_format((float) $detalle->renta_5ta, 2) }}</td></tr>
            </tbody>
        </table>

        <div class="totales">
            <div class="total"><span>Total ingresos</span><strong>S/ {{ number_format((float) $detalle->total_ingresos, 2) }}</strong></div>
            <div class="total"><span>Total descuentos</span><strong>S/ {{ number_format((float) $detalle->total_descuentos, 2) }}</strong></div>
            <div class="total neto"><span>Neto a pagar</span><strong>S/ {{ number_format((float) $detalle->neto, 2) }}</strong></div>
        </div>

        <p class="pie">Documento generado por el sistema EPAM HR. Jornada de referencia: 8 horas diarias (480 minutos); el refrigerio no computa como tiempo efectivo.</p>
    </div>
</div>
</body>
</html>
