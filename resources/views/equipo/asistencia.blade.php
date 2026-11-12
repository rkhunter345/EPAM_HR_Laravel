@extends('layouts.app')
@section('titulo', 'Asistencia del equipo')
@section('seccion', 'Supervisión')
@section('migas', 'Asistencia del equipo')

@section('contenido')
@php $h = fn ($t) => $t ? substr($t, 0, 5) : '—:—'; @endphp
<div class="v-cab">
    <div>
        <h1>Asistencia del equipo</h1>
        <p>Consulta de marcaciones del equipo asignado. La corrección de registros es exclusiva de Recursos Humanos.</p>
    </div>
</div>

<form method="GET" class="v-card v-mb" style="padding:12px 16px">
    <div class="v-barra-filtros">
        <div class="campo" style="max-width:180px"><label>Fecha</label><input type="date" name="fecha" value="{{ $fecha }}"></div>
        <button class="btn btn--pri btn--sm"><x-ui.icono name="filtrar" :size="13" /> Consultar</button>
        <span class="v-mini" style="margin-left:auto">{{ $filas->count() }} marcaciones · {{ $salidas->count() }} salidas temporales</span>
    </div>
</form>

<div class="v-kpis">
    <x-ui.kpi label="Marcaciones" :valor="$filas->count()" icono="reloj" tono="azul" sub="{{ $fecha }}" />
    <x-ui.kpi label="Salidas temporales" :valor="$salidas->count()" icono="pausa" tono="ambar" sub="Monitoreo pausado" />
    <x-ui.kpi label="Integrantes" :valor="$equipo->count()" icono="equipo" tono="violeta" sub="Equipo asignado" />
    <x-ui.kpi label="Sin marcación" :valor="max(0, $equipo->count() - $filas->count())" icono="ausencia" tono="rojo" sub="Requieren seguimiento" />
</div>

<div class="v-grid v-grid--lado">
    <x-ui.tarjeta titulo="Marcaciones del día" icono="reloj" nota="Origen, tardanza y horas efectivas" sin-relleno="1">
        <div class="v-tabla-wrap"><table class="tabla">
            <thead><tr><th>Trabajador</th><th>Área</th><th>Entrada</th><th>Refrigerio</th><th>Salida</th><th>Horas</th><th>Tardanza</th><th>Estado</th></tr></thead>
            <tbody>
            @forelse($filas as $f)
                <tr>
                    <td><strong>{{ $f->empleado->nombre_completo ?? '—' }}</strong><br><small class="v-mini">{{ $f->empleado->codigo ?? '' }}</small></td>
                    <td class="v-mini">{{ $f->empleado->departamento->nombre ?? '—' }}</td>
                    <td>{{ $h($f->hora_entrada) }}</td>
                    <td>{{ $h($f->hora_inicio_refrigerio) }}–{{ $h($f->hora_fin_refrigerio) }}</td>
                    <td>{{ $h($f->hora_salida) }}</td>
                    <td>{{ number_format($f->minutos_trabajados / 60, 1) }} h</td>
                    <td>{{ $f->tardanza_min }} min</td>
                    <td><x-ui.badge :valor="$f->estado" /></td>
                </tr>
            @empty
                <tr><td colspan="8" class="vacio">No hay marcaciones para la fecha consultada.</td></tr>
            @endforelse
            </tbody></table></div>
    </x-ui.tarjeta>

    <div>
        <x-ui.tarjeta titulo="Salidas temporales" icono="pausa" sin-relleno="1">
            <div class="v-lista">
                @forelse($salidas as $s)
                    <div class="v-lista__item">
                        <span class="v-avatar v-avatar--ambar"><x-ui.icono name="pausa" :size="14" /></span>
                        <div class="v-lista__txt"><strong>{{ $s->empleado->nombre_completo ?? '—' }}</strong><small>{{ $s->motivo }} · {{ $s->hora_salida?->format('H:i') }}</small></div>
                        <x-ui.badge :valor="$s->estado" />
                    </div>
                @empty
                    <div class="v-lista__item"><div class="v-lista__txt"><strong>Sin salidas</strong><small>Nadie registró pausas este día.</small></div></div>
                @endforelse
            </div>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Alcance del rol" icono="escudo">
            <div class="v-estado v-estado--info"><x-ui.icono name="escudo" :size="16" /><div><strong>Solo consulta</strong>El supervisor ve la asistencia del equipo, sin editar horas ni aprobar correcciones.</div></div>
            <p class="v-nota v-mt">Toda corrección exige motivo obligatorio y queda auditada con el valor anterior y el nuevo.</p>
        </x-ui.tarjeta>
    </div>
</div>
@endsection
