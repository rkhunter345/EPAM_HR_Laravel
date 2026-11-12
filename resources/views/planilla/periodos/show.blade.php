@extends('layouts.app')
@section('titulo', 'Planilla '.$periodo->periodo)
@section('migas', 'Nómina')
@section('contenido')
<div class="cab">
    <div><h1>Planilla · {{ $periodo->periodo }}</h1><p>Boletas calculadas con las marcaciones del período</p></div>
    <div class="cab__acciones">
        <x-ui.badge :valor="$periodo->estado" />
        <a href="{{ route('periodos.planilla', $periodo) }}" target="_blank" class="btn"><x-ui.icono name="reporte" :size="14" /> Planilla A4</a>
        <a href="{{ route('periodos.planilla-pdf', $periodo) }}" class="btn btn--pri"><x-ui.icono name="reporte" :size="14" /> Planilla PDF</a>
        <a href="{{ route('periodos.index') }}" class="btn">Volver</a>
        <form method="POST" action="{{ route('periodos.calcular', $periodo) }}">
            @csrf
            <button class="btn btn--pri"><x-ui.icono name="planilla" :size="14" /> Calcular planilla</button>
        </form>
    </div>
</div>

<div class="grid grid--4" style="margin-bottom:14px">
    <div class="kpi" style="margin:0"><div class="kpi__cabeza"><span class="kpi__icono tono-azul"><x-ui.icono name="empleados" :size="13" /></span><span class="kpi__label">Boletas</span></div><div class="kpi__valor">{{ $totalEmpleados }}</div></div>
    <div class="kpi" style="margin:0"><div class="kpi__cabeza"><span class="kpi__icono tono-verde"><x-ui.icono name="planilla" :size="13" /></span><span class="kpi__label">Ingresos</span></div><div class="kpi__valor">S/ {{ number_format((float) $periodo->total_ingresos, 2) }}</div></div>
    <div class="kpi" style="margin:0"><div class="kpi__cabeza"><span class="kpi__icono tono-rojo"><x-ui.icono name="ausencia" :size="13" /></span><span class="kpi__label">Descuentos</span></div><div class="kpi__valor">S/ {{ number_format((float) $periodo->total_descuentos, 2) }}</div></div>
    <div class="kpi" style="margin:0"><div class="kpi__cabeza"><span class="kpi__icono tono-violeta"><x-ui.icono name="check" :size="13" /></span><span class="kpi__label">Neto a pagar</span></div><div class="kpi__valor">S/ {{ number_format((float) $periodo->total_neto, 2) }}</div></div>
</div>

<div class="card">
    <div class="card__cab"><div><h2>Detalle por empleado</h2><small>Promedio por boleta: S/ {{ number_format($promedio, 2) }} · Asig. fam. y aportes de empresa incluidos</small></div></div>
    <div class="tabla-wrap">
        <table class="tabla">
            <thead><tr><th>Empleado</th><th>Básico</th><th>Asig. fam.</th><th>Extras/Bono</th><th>Bruto</th><th>Tard./Falta</th><th>Pensión</th><th>Renta 5ta</th><th>Neto</th><th>EsSalud</th><th>SCTR</th><th>Costo empresa</th><th></th></tr></thead>
            <tbody>
            @forelse($periodo->detalles as $d)
                <tr>
                    <td><div class="celda-persona"><span class="avatar avatar--foto">{{ $d->empleado->iniciales() ?? '?' }}</span>
                        <div class="celda-persona__datos"><strong>{{ $d->empleado->nombre_completo ?? '—' }}</strong>
                        <small>{{ $d->empleado->codigo ?? '' }} · {{ $d->tardanzas_min }} min tardanza · {{ $d->ausencias }} faltas</small></div></div></td>
                    <td>S/ {{ number_format((float) $d->basico, 2) }} <small style="color:var(--texto-3)">{{ $d->afp }}</small></td>
                    <td>S/ {{ number_format((float) $d->asig_familiar, 2) }}</td>
                    <td>S/ {{ number_format((float) $d->horas_extras + (float) $d->bono_asistencia, 2) }}</td>
                    <td>S/ {{ number_format((float) $d->total_ingresos, 2) }}</td>
                    <td style="color:var(--rojo)">S/ {{ number_format((float) $d->desc_tardanza + (float) $d->desc_ausencia, 2) }}</td>
                    <td style="color:var(--rojo)">S/ {{ number_format((float) $d->aporte_pension, 2) }}</td>
                    <td style="color:var(--rojo)">S/ {{ number_format((float) $d->renta_5ta, 2) }}</td>
                    <td><strong>S/ {{ number_format((float) $d->neto, 2) }}</strong></td>
                    <td>S/ {{ number_format((float) $d->essalud, 2) }}</td>
                    <td>S/ {{ number_format((float) $d->sctr, 2) }}</td>
                    <td>S/ {{ number_format((float) $d->costo_empresa, 2) }}</td>
                    <td>
                        <div class="acciones">
                            <a class="btn btn--sm" href="{{ route('periodos.recibo', [$periodo, $d]) }}" target="_blank">Recibo</a>
                            <a class="btn btn--sm btn--pri" href="{{ route('periodos.boleta-pdf', [$periodo, $d]) }}">Boleta PDF</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="13" class="vacio">Período sin boletas. Presione «Calcular planilla» para generarlas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
