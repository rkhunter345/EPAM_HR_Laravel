@extends('layouts.app')
@section('titulo', 'Reportes del equipo')
@section('seccion', 'Supervisión')
@section('migas', 'Reportes del equipo')

@section('contenido')
@php $max = max(1, collect($serie)->max(fn ($s) => max($s['asistio'], $s['tardanza'], $s['ausente']))); @endphp
<div class="v-cab">
    <div>
        <h1>Reportes del equipo</h1>
        <p>Indicadores de asistencia, puntualidad y ausencias del equipo asignado. La exportación requiere autorización adicional.</p>
    </div>
</div>

<form method="GET" class="v-card v-mb" style="padding:12px 16px">
    <div class="v-barra-filtros">
        <div class="campo" style="max-width:170px"><label>Desde</label><input type="date" name="desde" value="{{ $desde }}"></div>
        <div class="campo" style="max-width:170px"><label>Hasta</label><input type="date" name="hasta" value="{{ $hasta }}"></div>
        <button class="btn btn--pri btn--sm"><x-ui.icono name="filtrar" :size="13" /> Generar</button>
        <span class="v-mini" style="margin-left:auto">Periodo: {{ $desde }} al {{ $hasta }}</span>
    </div>
</form>

<div class="v-kpis">
    <x-ui.kpi label="Tasa de asistencia" :valor="$asistencia" unidad="%" icono="check" tono="verde" sub="Asistió y tardanza" />
    <x-ui.kpi label="Tardanzas" :valor="$tardanzas" icono="reloj" tono="ambar" sub="Registros fuera de tolerancia" />
    <x-ui.kpi label="Ausencias" :valor="$ausencias" icono="ausencia" tono="rojo" sub="Sin marcación" />
    <x-ui.kpi label="Horas efectivas" :valor="round($minutos / 60)" unidad="h" icono="actividad" tono="azul" sub="Total del periodo" />
</div>

<div class="v-grid v-grid--lado">
    <div>
        <x-ui.tarjeta titulo="Evolución diaria" icono="actividad" nota="Asistencia, tardanza y ausencia por día">
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

        <x-ui.tarjeta titulo="Detalle por integrante" icono="equipo" sin-relleno="1">
            <div class="v-tabla-wrap"><table class="tabla">
                <thead><tr><th>Trabajador</th><th>Código</th><th>Tardanzas</th><th>Ausencias</th><th>Puntualidad</th></tr></thead>
                <tbody>
                @foreach($porEmpleado as $fila)
                    <tr>
                        <td><div class="v-persona"><span class="v-avatar">{{ $fila['empleado']->iniciales() }}</span><div><strong>{{ $fila['empleado']->nombre_completo }}</strong></div></div></td>
                        <td class="v-mini">{{ $fila['empleado']->codigo }}</td>
                        <td>{{ $fila['tardanzas'] }}</td>
                        <td>{{ $fila['ausencias'] }}</td>
                        <td><x-ui.badge :valor="$fila['puntualidad'] >= 90 ? 'Bueno' : ($fila['puntualidad'] >= 70 ? 'Regular' : 'Bajo')" /> {{ $fila['puntualidad'] }}%</td>
                    </tr>
                @endforeach
                </tbody></table></div>
        </x-ui.tarjeta>
    </div>

    <div>
        <x-ui.tarjeta titulo="Resumen del periodo" icono="reporte">
            <div class="v-dato"><span>Justificados</span><strong>{{ $justificados }}</strong></div>
            <div class="v-dato"><span>Integrantes</span><strong>{{ $equipo->count() }}</strong></div>
            <div class="v-dato"><span>Tardanzas</span><strong>{{ $tardanzas }}</strong></div>
            <div class="v-dato"><span>Ausencias</span><strong>{{ $ausencias }}</strong></div>
        </x-ui.tarjeta>
        <x-ui.tarjeta titulo="Exportación" icono="descargar">
            <div class="v-estado v-estado--warn"><x-ui.icono name="escudo" :size="16" /><div><strong>Requiere autorización aparte</strong>La exportación de reportes se autoriza expresamente y queda registrada en la auditoría.</div></div>
            <p class="v-nota v-mt">Solicita la autorización a Recursos Humanos indicando el periodo y el destino del archivo.</p>
        </x-ui.tarjeta>
    </div>
</div>
@endsection
