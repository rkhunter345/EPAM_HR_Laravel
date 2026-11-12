@extends('layouts.app')
@section('titulo', 'Boletas de pago')
@section('seccion', 'Recursos Humanos')
@section('migas', 'Boletas de pago')

@section('contenido')
<div class="v-cab">
    <div>
        <h1>Boletas de pago</h1>
        <p>Publicación y consulta de boletas por periodo. Cada descarga queda registrada en la auditoría del sistema.</p>
    </div>
</div>

<div class="v-kpis">
    <x-ui.kpi label="Boletas generadas" :valor="$generadas" icono="planilla" tono="azul" sub="{{ $periodo->periodo ?? 'sin periodo' }}" />
    <x-ui.kpi label="Publicadas" :valor="$publicadas" icono="check" tono="verde" sub="Periodo pagado" />
    <x-ui.kpi label="Pendientes" :valor="$pendientes" icono="reloj" tono="ambar" sub="Trabajadores sin boleta" />
    <x-ui.kpi label="Incidencias" :valor="$incidencias" icono="alerta" tono="rojo" sub="Neto menor o igual a cero" />
</div>

<x-ui.tarjeta titulo="Boletas del periodo {{ $periodo->periodo ?? '' }}" icono="planilla" nota="Descarga en PDF con desglose de ingresos y descuentos" sin-relleno="1">
    <div class="v-tabla-wrap"><table class="tabla">
        <thead><tr><th>Trabajador</th><th>Ingresos</th><th>Descuentos</th><th>Neto</th><th>Tardanzas</th><th>Ausencias</th><th>Boleta</th></tr></thead>
        <tbody>
        @forelse($detalles as $d)
            <tr>
                <td><div class="v-persona"><span class="v-avatar">{{ $d->empleado?->iniciales() }}</span><div><strong>{{ $d->empleado->nombre_completo ?? '—' }}</strong><small>{{ $d->empleado->codigo ?? '' }}</small></div></div></td>
                <td>S/ {{ number_format((float) $d->total_ingresos, 2) }}</td>
                <td>S/ {{ number_format((float) $d->total_descuentos, 2) }}</td>
                <td><strong>S/ {{ number_format((float) $d->neto, 2) }}</strong></td>
                <td>{{ $d->tardanzas_min ?? 0 }} min</td>
                <td>{{ $d->ausencias ?? 0 }}</td>
                <td>@if($periodo)<a class="btn btn--sm btn--pri" href="{{ route('rrhh.boleta.pdf', [$periodo->id, $d->id]) }}"><x-ui.icono name="descargar" :size="12" /> Descargar</a>@endif</td>
            </tr>
        @empty
            <tr><td colspan="7" class="vacio">No hay boletas generadas en este periodo.</td></tr>
        @endforelse
        </tbody></table></div>
</x-ui.tarjeta>

<p class="v-nota v-mt"><x-ui.icono name="escudo" :size="13" /> La descarga de boletas se audita con usuario, fecha y trabajador consultado; el acceso a salarios es exclusivo del rol de RR. HH. con permiso de nómina.</p>
@endsection
