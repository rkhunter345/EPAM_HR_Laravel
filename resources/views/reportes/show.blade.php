@extends('layouts.app')
@section('titulo', $reporte->tipo)
@section('migas', 'Nómina')
@section('contenido')
<div class="cab">
    <div><h1>{{ $reporte->tipo }}</h1><p>{{ $reporte->empleado->nombre_completo ?? 'Todos los empleados' }} · {{ $desde }} a {{ $hasta }}</p></div>
    <div class="cab__acciones">
        <a href="{{ route('reportes.index') }}" class="btn">Volver</a>
        <a href="{{ route('reportes.exportar', [$reporte, 'csv']) }}?desde={{ $desde }}&hasta={{ $hasta }}" class="btn">Excel (CSV)</a>
        <a href="{{ route('reportes.exportar', [$reporte, 'horizontal']) }}?desde={{ $desde }}&hasta={{ $hasta }}" target="_blank" class="btn">A4 horizontal</a>
        <button class="btn btn--pri" onclick="window.print()"><x-ui.icono name="reporte" :size="14" /> PDF / Imprimir (A4)</button>
    </div>

    <form method="GET" class="filtros" style="margin-bottom:12px">
        <div class="campo"><label>Desde</label><input type="date" name="desde" value="{{ $desde }}"></div>
        <div class="campo"><label>Hasta</label><input type="date" name="hasta" value="{{ $hasta }}"></div>
        <button class="btn btn--pri">Aplicar fechas</button>
        <small style="color:var(--texto-3);align-self:center">El rango incluye el último día completo.</small>
    </form>
</div>

<div class="card enc-empresa">
    <div class="card__cuerpo enc-empresa__fila">
        <div class="enc-empresa__datos">
            <strong>{{ \App\Models\Organizacion\Compania::first()?->nombre ?? 'MULTISERVICIOS EPAM E.I.R.L.' }}</strong>
            <small>RUC {{ \App\Models\Organizacion\Compania::first()?->ruc ?? '—' }} · Reporte emitido el {{ now()->format('d/m/Y H:i') }}</small>
        </div>
        <div class="enc-empresa__meta">
            <strong>{{ $reporte->tipo }}</strong>
            <small>{{ $reporte->empleado->nombre_completo ?? 'Todos los empleados' }} · {{ $desde }} a {{ $hasta }}</small>
        </div>
    </div>
</div>

<div class="card">
    <div class="card__cab"><div><h2>Marcaciones del período</h2><small>{{ $asistencias->count() }} registros</small></div></div>
    <div class="tabla-wrap">
        <table class="tabla">
            <thead><tr><th>Empleado</th><th>Fecha</th><th>Entrada</th><th>Salida</th><th>Tardanza</th><th>Horas</th><th>Estado</th></tr></thead>
            <tbody>
            @forelse($asistencias as $a)
                <tr>
                    <td><strong>{{ $a->empleado->nombre_completo ?? '—' }}</strong><br><small style="color:var(--texto-3)">{{ $a->empleado->codigo ?? '' }}</small></td>
                    <td>{{ $a->fecha->format('d/m/Y') }}</td>
                    <td>{{ $a->hora_entrada ?? '—' }}</td>
                    <td>{{ $a->hora_salida ?? '—' }}</td>
                    <td>{{ $a->tardanza_min }} min</td>
                    <td>{{ number_format($a->minutos_trabajados / 60, 2) }}</td>
                    <td><x-ui.badge :valor="$a->estado" /></td>
                </tr>
            @empty
                <tr><td colspan="7" class="vacio">Sin marcaciones en el período.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card__cab"><div><h2>Planilla de remuneraciones</h2><small>{{ $planilla->count() }} boletas</small></div></div>
    <div class="tabla-wrap">
        <table class="tabla">
            <thead><tr><th>Período</th><th>Empleado</th><th>Ingresos</th><th>Descuentos</th><th>Neto</th></tr></thead>
            <tbody>
            @forelse($planilla as $p)
                <tr>
                    <td>{{ $p->periodo->periodo ?? '—' }}</td>
                    <td><strong>{{ $p->empleado->nombre_completo ?? '—' }}</strong></td>
                    <td>S/ {{ number_format((float) $p->total_ingresos, 2) }}</td>
                    <td>S/ {{ number_format((float) $p->total_descuentos, 2) }}</td>
                    <td><strong>S/ {{ number_format((float) $p->neto, 2) }}</strong></td>
                </tr>
            @empty
                <tr><td colspan="5" class="vacio">Sin boletas generadas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
