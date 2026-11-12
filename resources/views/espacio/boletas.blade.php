@extends('layouts.app')
@section('titulo', 'Mis boletas de pago')
@section('seccion', 'Mi espacio')
@section('migas', 'Boletas de pago')

@section('contenido')
<div class="v-cab">
    <div>
        <h1>Mis boletas de pago</h1>
        <p>Descarga el PDF de tus boletas. Cada descarga queda registrada en la auditoría del sistema.</p>
    </div>
</div>

<div class="v-kpis">
    <x-ui.kpi label="Boletas disponibles" :valor="$detalles->count()" icono="planilla" tono="azul" sub="Periodos publicados" />
    <x-ui.kpi label="Último neto" :valor="'S/ '.number_format((float) ($detalles->first()->neto ?? 0), 2)" icono="dinero" tono="verde" sub="{{ $detalles->first()->periodo->periodo ?? 'Sin periodo' }}" />
    <x-ui.kpi label="Ingresos acumulados" :valor="'S/ '.number_format((float) $detalles->sum('total_ingresos'), 2)" icono="actividad" tono="violeta" sub="Total histórico" />
    <x-ui.kpi label="Descuentos acumulados" :valor="'S/ '.number_format((float) $detalles->sum('total_descuentos'), 2)" icono="ausencia" tono="ambar" sub="Pensiones, renta y adelantos" />
</div>

<x-ui.tarjeta titulo="Historial de boletas" icono="planilla" nota="Solo se muestran los periodos con boleta generada" sin-relleno="1">
    <div class="v-tabla-wrap"><table class="tabla">
        <thead><tr><th>Periodo</th><th>Estado</th><th>Básico</th><th>Ingresos</th><th>Descuentos</th><th>Neto</th><th>Acción</th></tr></thead>
        <tbody>
        @forelse($detalles as $d)
            <tr>
                <td><strong>{{ $d->periodo->periodo ?? '—' }}</strong><br><small class="v-mini">{{ $d->periodo->fecha_pago?->format('d/m/Y') ?? 'sin fecha de pago' }}</small></td>
                <td><x-ui.badge :valor="$d->periodo->estado ?? 'Borrador'" /></td>
                <td>S/ {{ number_format((float) $d->basico, 2) }}</td>
                <td>S/ {{ number_format((float) $d->total_ingresos, 2) }}</td>
                <td>S/ {{ number_format((float) $d->total_descuentos, 2) }}</td>
                <td><strong>S/ {{ number_format((float) $d->neto, 2) }}</strong></td>
                <td><a class="btn btn--sm" href="{{ route('minomina.boleta', $d) }}"><x-ui.icono name="descargar" :size="13" /> PDF</a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="vacio">Aún no hay boletas publicadas para tu expediente.</td></tr>
        @endforelse
        </tbody></table></div>
</x-ui.tarjeta>

<div class="v-grid v-grid--2 v-mt">
    <x-ui.tarjeta titulo="Cómo se calcula tu boleta" icono="dinero">
        <div class="v-dato"><span>Ingresos</span><strong>Básico + asignación familiar + horas extras + bono</strong></div>
        <div class="v-dato"><span>Descuentos</span><strong>Tardanzas, ausencias, pensión, renta de 5.ª y adelantos</strong></div>
        <div class="v-dato"><span>Neto</span><strong>Ingresos − descuentos</strong></div>
    </x-ui.tarjeta>
    <x-ui.tarjeta titulo="Privacidad de la información" icono="escudo">
        <div class="v-estado v-estado--info"><x-ui.icono name="escudo" :size="16" /><div><strong>Solo tus boletas</strong>Ningún otro rol sin permiso específico puede ver tu remuneración; las descargas quedan auditadas.</div></div>
    </x-ui.tarjeta>
</div>
@endsection
