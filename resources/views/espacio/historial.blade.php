@extends('layouts.app')
@section('titulo', 'Mi historial de asistencia')
@section('seccion', 'Mi espacio')
@section('migas', 'Historial')

@section('contenido')
@php $h = fn ($t) => $t ? substr($t, 0, 5) : '—:—'; @endphp
<div class="v-cab">
    <div>
        <h1>Mi historial de asistencia</h1>
        <p>Consulta diaria y mensual de tus marcaciones, tardanzas, refrigerios y salidas temporales registradas.</p>
    </div>
    <div class="v-cab__acc">
        <a class="btn" href="{{ route('espacio.asistencia') }}"><x-ui.icono name="reloj" :size="14" /> Ir a marcar</a>
    </div>
</div>

<form method="GET" class="v-card v-mb" style="padding:12px 16px">
    <div class="v-barra-filtros">
        <div class="campo" style="max-width:170px"><label>Desde</label><input type="date" name="desde" value="{{ $desde }}"></div>
        <div class="campo" style="max-width:170px"><label>Hasta</label><input type="date" name="hasta" value="{{ $hasta }}"></div>
        <button class="btn btn--pri btn--sm"><x-ui.icono name="filtrar" :size="13" /> Aplicar filtro</button>
        <span class="v-mini" style="margin-left:auto">{{ $filas->count() }} registros encontrados</span>
    </div>
</form>

<div class="v-kpis">
    <x-ui.kpi label="Días con asistencia" :valor="$dias" icono="check" tono="verde" sub="Asistió, tardanza o justificado" />
    <x-ui.kpi label="Tardanzas" :valor="$tardanzas" icono="ausencia" tono="ambar" sub="Fuera de la tolerancia" />
    <x-ui.kpi label="Horas acumuladas" :valor="round($minutos / 60, 1)" unidad="h" icono="reloj" tono="azul" sub="Minutos efectivos trabajados" />
    <x-ui.kpi label="Refrigerios" :valor="$refrigerios" unidad="min" icono="pausa" tono="violeta" sub="Tiempo total de refrigerio" />
</div>

<div class="v-grid v-grid--lado">
    <div>
        <x-ui.tarjeta titulo="Detalle diario" icono="calendario" nota="{{ $desde }} al {{ $hasta }}" sin-relleno="1">
            <div class="v-tabla-wrap"><table class="tabla">
                <thead><tr><th>Fecha</th><th>Entrada</th><th>Refrigerio</th><th>Salida</th><th>Horas</th><th>Tardanza</th><th>Estado</th></tr></thead>
                <tbody>
                @forelse($filas as $f)
                    <tr>
                        <td><strong>{{ $f->fecha->format('d/m/Y') }}</strong><br><small class="v-mini">{{ $f->fecha->locale('es')->translatedFormat('D') }}</small></td>
                        <td>{{ $h($f->hora_entrada) }}</td>
                        <td>{{ $h($f->hora_inicio_refrigerio) }}–{{ $h($f->hora_fin_refrigerio) }}</td>
                        <td>{{ $h($f->hora_salida) }}</td>
                        <td>{{ number_format($f->minutos_trabajados / 60, 1) }} h</td>
                        <td>{{ $f->tardanza_min }} min</td>
                        <td><x-ui.badge :valor="$f->estado" /></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="vacio">No hay registros en el rango seleccionado.</td></tr>
                @endforelse
                </tbody></table></div>
        </x-ui.tarjeta>
    </div>
    <div>
        <x-ui.tarjeta titulo="Salidas temporales" icono="pausa" nota="Pausas del monitoreo autorizadas" sin-relleno="1">
            <div class="v-lista">
                @forelse($salidas as $s)
                    <div class="v-lista__item">
                        <span class="v-lista__ico tono-ambar"><x-ui.icono name="pausa" :size="14" /></span>
                        <div class="v-lista__txt">
                            <strong>{{ $s->motivo }}</strong>
                            <small>{{ $s->hora_salida?->format('d/m/Y H:i') }} · retorno {{ $s->hora_retorno?->format('H:i') ?? 'pendiente' }}</small>
                        </div>
                        <x-ui.badge :valor="$s->estado" />
                    </div>
                @empty
                    <div class="v-lista__item"><div class="v-lista__txt"><strong>Sin salidas temporales</strong><small>No registraste pausas en el rango consultado.</small></div></div>
                @endforelse
            </div>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Resumen del mes" icono="actividad">
            <div class="v-dato"><span>Días registrados (60 últimos)</span><strong>{{ $meses->count() }}</strong></div>
            <div class="v-dato"><span>Tardanzas acumuladas</span><strong>{{ $meses->where('tardanza_min', '>', 0)->count() }}</strong></div>
            <div class="v-dato"><span>Horas efectivas</span><strong>{{ round($meses->sum('minutos_trabajados') / 60, 1) }} h</strong></div>
            <div class="v-dato"><span>Justificados</span><strong>{{ $meses->where('estado', 'Justificado')->count() }}</strong></div>
            <p class="v-nota v-mt"><x-ui.icono name="escudo" :size="13" /> Si detectas un error en tu registro, presenta una justificación para que RR. HH. pueda corregirlo con trazabilidad.</p>
        </x-ui.tarjeta>
    </div>
</div>
@endsection
