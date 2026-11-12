<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>{{ $reporte->tipo }} · {{ $desde }} a {{ $hasta }}</title>
<style>
@page{size:A4 landscape;margin:8mm}
*{box-sizing:border-box}
body{font-family:"Segoe UI",Arial,sans-serif;color:#1f272e;margin:0;padding:10px;background:#eef0f4;font-size:9px}
.btns{max-width:281mm;margin:0 auto 8px;display:flex;gap:8px;justify-content:flex-end}
.btn{font:inherit;font-size:11px;padding:6px 14px;border-radius:7px;border:1px solid #c8cdd4;background:#fff;cursor:pointer;color:#1f272e;text-decoration:none}
.btn-pri{background:#2490ef;border-color:#2490ef;color:#fff}
.hoja{width:281mm;min-height:192mm;margin:0 auto;background:#fff;padding:8mm;box-shadow:0 2px 10px rgba(16,24,40,.08)}
.enc{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:2px solid #1f272e;padding-bottom:6px;margin-bottom:8px}
.enc h1{margin:0;font-size:13px}
.enc small{display:block;color:#5b6470;margin-top:2px}
table{width:100%;border-collapse:collapse}
th,td{border:1px solid #c8cdd4;padding:3.5px 5px;font-size:8.2px;text-align:right}
th{background:#f0f2f5;font-weight:700;text-align:center;text-transform:uppercase;font-size:7.4px}
th.txt,td.txt{text-align:left}
tbody tr:nth-child(even) td{background:#fafbfc}
@media print{body{background:#fff;padding:0}.btns{display:none}.hoja{box-shadow:none;width:auto;min-height:auto}}
</style>
</head>
<body>
<div class="btns">
    <a class="btn" href="javascript:history.back()">Volver</a>
    <button class="btn btn-pri" onclick="window.print()">Imprimir (A4 horizontal)</button>
</div>
<div class="hoja">
    <div class="enc">
        <div>
            <h1>MULTISERVICIOS EPAM E.I.R.L.</h1>
            <small>RUC 20481234567 · {{ $reporte->tipo }}</small>
        </div>
        <div style="text-align:right">
            <small><strong>{{ $reporte->empleado->nombre_completo ?? 'Todos los empleados' }}</strong></small>
            <small>Del {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}</small>
            <small>Emitido: {{ now()->format('d/m/Y H:i') }} · EPAM HR</small>
        </div>
    </div>
    <table>
        <thead><tr>
            <th class="txt">Empleado</th><th class="txt">Código</th><th>Fecha</th><th>Entrada</th><th>Salida</th>
            <th>Tardanza</th><th>Horas</th><th>Estado</th>
        </tr></thead>
        <tbody>
        @forelse($filas as $a)
            <tr>
                <td class="txt">{{ $a->empleado->nombre_completo ?? '—' }}</td>
                <td class="txt">{{ $a->empleado->codigo ?? '—' }}</td>
                <td>{{ $a->fecha->format('d/m/Y') }}</td>
                <td>{{ $a->hora_entrada ?? '—' }}</td>
                <td>{{ $a->hora_salida ?? '—' }}</td>
                <td>{{ $a->tardanza_min }} min</td>
                <td>{{ number_format($a->minutos_trabajados / 60, 2) }}</td>
                <td>{{ $a->estado }}</td>
            </tr>
        @empty
            <tr><td colspan="8" style="text-align:center;padding:12px">Sin marcaciones en el rango seleccionado.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
</body>
</html>
