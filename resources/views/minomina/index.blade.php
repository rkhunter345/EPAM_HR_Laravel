@extends('layouts.app')
@section('titulo', 'Mi nómina')
@section('migas', 'Nómina')
@section('contenido')
<div class="cab">
    <div>
        <h1>Mi nómina</h1>
        <p>Boletas de pago de {{ $empleado->nombre_completo }} ({{ $empleado->codigo }})</p>
    </div>
</div>

<div class="grid grid--4" style="margin-bottom:14px">
    <div class="kpi"><div class="kpi__cabeza"><span class="kpi__icono tono-azul"><x-ui.icono name="empleados" :size="13" /></span><span class="kpi__label">Cargo</span></div><div class="kpi__valor" style="font-size:14px">{{ $empleado->designacion->nombre ?? '—' }}</div><div class="kpi__sub">{{ $empleado->departamento->nombre ?? '—' }}</div></div>
    <div class="kpi"><div class="kpi__cabeza"><span class="kpi__icono tono-verde"><x-ui.icono name="planilla" :size="13" /></span><span class="kpi__label">Salario mensual</span></div><div class="kpi__valor">S/ {{ number_format((float) $empleado->salario_mensual, 2) }}</div><div class="kpi__sub">Sistema: {{ $empleado->afp }}</div></div>
    <div class="kpi"><div class="kpi__cabeza"><span class="kpi__icono tono-violeta"><x-ui.icono name="check" :size="13" /></span><span class="kpi__label">Boletas emitidas</span></div><div class="kpi__valor">{{ $detalles->total() }}</div><div class="kpi__sub">Períodos pagados y en revisión</div></div>
    <div class="kpi"><div class="kpi__cabeza"><span class="kpi__icono tono-ambar"><x-ui.icono name="planilla" :size="13" /></span><span class="kpi__label">Último neto</span></div><div class="kpi__valor">S/ {{ number_format((float) ($detalles->first()?->neto ?? 0), 2) }}</div><div class="kpi__sub">{{ $detalles->first()?->periodo?->periodo ?? '—' }}</div></div>
</div>

<div class="card">
    <div class="card__cab"><div><h2>Mis boletas de pago</h2><small>Descarga tu boleta en PDF con un clic</small></div></div>
    <div class="tabla-wrap">
        <table class="tabla">
            <thead><tr><th>Período</th><th>Estado</th><th>Básico</th><th>Asig. fam.</th><th>Extras/Bono</th><th>Bruto</th><th>Descuentos</th><th>Neto</th><th style="text-align:right">Boleta</th></tr></thead>
            <tbody>
            @forelse($detalles as $d)
                <tr>
                    <td><strong>{{ $d->periodo->periodo ?? '—' }}</strong></td>
                    <td><x-ui.badge :valor="$d->periodo->estado ?? 'Borrador'" /></td>
                    <td>S/ {{ number_format((float) $d->basico, 2) }}</td>
                    <td>S/ {{ number_format((float) $d->asig_familiar, 2) }}</td>
                    <td>S/ {{ number_format((float) $d->horas_extras + (float) $d->bono_asistencia, 2) }}</td>
                    <td>S/ {{ number_format((float) $d->total_ingresos, 2) }}</td>
                    <td style="color:var(--rojo)">S/ {{ number_format((float) $d->total_descuentos, 2) }}</td>
                    <td><strong>S/ {{ number_format((float) $d->neto, 2) }}</strong></td>
                    <td style="text-align:right">
                        <a class="btn btn--sm btn--pri" href="{{ route('minomina.boleta', $d) }}"><x-ui.icono name="reporte" :size="13" /> Boleta PDF</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="vacio">Aún no tienes boletas emitidas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card__pie">
        <small style="color:var(--texto-3)">{{ $detalles->total() }} boletas</small>
        {{ $detalles->links('components.crud.paginacion') }}
    </div>
</div>
@endsection
