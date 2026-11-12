@extends('layouts.app')
@section('titulo', 'Reportes de Recursos Humanos')
@section('seccion', 'Recursos Humanos')
@section('migas', 'Reportes')

@section('contenido')
@php $max = max(1, collect($serie)->max(fn ($s) => max($s['asistio'], $s['tardanza'], $s['ausente']))); @endphp
<div class="v-cab">
    <div>
        <h1>Reportes de Recursos Humanos</h1>
        <p>Indicadores del periodo: asistencia, tardanzas, ausencias, horas efectivas y evolución de la nómina.</p>
    </div>
</div>

<form method="GET" class="v-card v-mb" style="padding:12px 16px">
    <div class="v-barra-filtros">
        <div class="campo" style="max-width:170px"><label>Desde</label><input type="date" name="desde" value="{{ $desde }}"></div>
        <div class="campo" style="max-width:170px"><label>Hasta</label><input type="date" name="hasta" value="{{ $hasta }}"></div>
        <button class="btn btn--pri btn--sm"><x-ui.icono name="filtrar" :size="13" /> Generar reporte</button>
        <span class="v-mini" style="margin-left:auto">{{ $filas->count() }} registros · periodo {{ $desde }} a {{ $hasta }}</span>
    </div>
</form>

<div class="v-kpis">
    <x-ui.kpi label="Tasa de asistencia" :valor="$asistencia" unidad="%" icono="check" tono="verde" sub="Sobre registros del periodo" />
    <x-ui.kpi label="Tardanzas" :valor="$tardanzas" icono="reloj" tono="ambar" sub="Fuera de tolerancia" />
    <x-ui.kpi label="Ausencias" :valor="$ausencias" icono="ausencia" tono="rojo" sub="Sin marcación" />
    <x-ui.kpi label="Horas efectivas" :valor="$horas" unidad="h" icono="actividad" tono="azul" sub="Total del periodo" />
</div>

<div class="v-grid v-grid--lado">
    <div>
        <x-ui.tarjeta titulo="Evolución del periodo" icono="actividad" nota="Comparativo diario de asistencia, tardanza y ausencia">
            <div class="v-barras">
                @forelse($serie as $s)
                    <div class="v-barras__col">
                        <div class="v-barras__b" style="height:{{ max(6, (int) round($s['asistio'] / $max * 100)) }}%" title="Asistió: {{ $s['asistio'] }}"></div>
                        <div class="v-barras__b tono-ambar" style="height:{{ max(4, (int) round($s['tardanza'] / $max * 100)) }}%" title="Tardanza: {{ $s['tardanza'] }}"></div>
                        <div class="v-barras__b tono-rojo" style="height:{{ max(3, (int) round($s['ausente'] / $max * 100)) }}%" title="Ausente: {{ $s['ausente'] }}"></div>
                        <span class="v-mini">{{ $s['etiqueta'] }}</span>
                    </div>
                @empty
                    <p class="vacio">Sin datos en el periodo.</p>
                @endforelse
            </div>
            <div class="v-leyenda"><span><i class="tono-verde"></i> Asistió</span><span><i class="tono-ambar"></i> Tardanza</span><span><i class="tono-rojo"></i> Ausente</span></div>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Nómina de los últimos periodos" icono="dinero" sin-relleno="1">
            <div class="v-tabla-wrap"><table class="tabla">
                <thead><tr><th>Periodo</th><th>Estado</th><th>Ingresos</th><th>Descuentos</th><th>Neto</th></tr></thead>
                <tbody>
                @forelse($nomina as $n)
                    <tr>
                        <td><strong>{{ $n->periodo }}</strong></td>
                        <td><x-ui.badge :valor="$n->estado" /></td>
                        <td>S/ {{ number_format((float) $n->total_ingresos, 2) }}</td>
                        <td>S/ {{ number_format((float) $n->total_descuentos, 2) }}</td>
                        <td><strong>S/ {{ number_format((float) $n->total_neto, 2) }}</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="vacio">Sin periodos de nómina.</td></tr>
                @endforelse
                </tbody></table></div>
        </x-ui.tarjeta>
    </div>

    <div>
        <x-ui.tarjeta titulo="Personal por departamento" icono="organigrama" sin-relleno="1">
            <div class="v-tabla-wrap"><table class="tabla">
                <thead><tr><th>Departamento</th><th>Colaboradores</th></tr></thead>
                <tbody>
                @foreach($departamentos as $d)
                    <tr><td>{{ $d->nombre }}</td><td>{{ $d->empleados_count }}</td></tr>
                @endforeach
                </tbody></table></div>
        </x-ui.tarjeta>
        <x-ui.tarjeta titulo="Exportación auditable" icono="descargar">
            <div class="v-estado v-estado--warn"><x-ui.icono name="escudo" :size="16" /><div><strong>Requiere autorización</strong>La exportación de reportes se autoriza expresamente y queda registrada con usuario, fecha y alcance.</div></div>
            <p class="v-nota v-mt">Los reportes de nómina incluyen datos sensibles: su difusión se limita a los destinatarios autorizados.</p>
        </x-ui.tarjeta>
    </div>
</div>
@endsection
