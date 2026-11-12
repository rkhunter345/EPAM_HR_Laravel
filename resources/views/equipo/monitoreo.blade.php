@extends('layouts.app')
@section('titulo', 'Monitoreo del equipo')
@section('seccion', 'Supervisión')
@section('migas', 'Monitoreo del equipo')

@section('contenido')
<div class="v-cab">
    <div>
        <h1>Monitoreo del equipo</h1>
        <p>Eventos de presencia y uso de celular detectados en las estaciones del equipo. No se identifican rostros ni se graba audio.</p>
    </div>
    <div class="v-cab__acc"><span class="v-pill is-active"><x-ui.icono name="reloj" :size="12" /> Tolerancia {{ $tolerancia }} min</span></div>
</div>

<div class="v-kpis">
    <x-ui.kpi label="Eventos registrados" :valor="$eventos->count()" icono="actividad" tono="azul" sub="Últimos 12 eventos" />
    <x-ui.kpi label="Salidas temporales activas" :valor="$salidas->count()" icono="pausa" tono="ambar" sub="Monitoreo pausado" />
    <x-ui.kpi label="Integrantes monitoreados" :valor="$equipo->count()" icono="equipo" tono="violeta" sub="Con estación asignada" />
    <x-ui.kpi label="Tolerancia aplicada" :valor="$tolerancia" unidad="min" icono="reloj" tono="verde" sub="Antes de generar alerta" />
</div>

<div class="v-grid v-grid--lado">
    <x-ui.tarjeta titulo="Eventos del equipo" icono="camara" nota="Cada evento requiere revisión humana antes de cualquier medida" sin-relleno="1">
        <div class="v-tabla-wrap"><table class="tabla">
            <thead><tr><th>Trabajador</th><th>Evento</th><th>Estación</th><th>Fecha y hora</th><th>Duración</th><th>Estado</th></tr></thead>
            <tbody>
            @forelse($eventos as $ev)
                <tr>
                    <td><strong>{{ $ev->empleado->nombre_completo ?? 'Sin asignar' }}</strong></td>
                    <td>{{ ['ausencia_puesto'=>'Ausencia del puesto','uso_celular'=>'Uso de celular','regreso'=>'Regreso','marcacion_qr'=>'Marcación QR'][$ev->tipo] ?? $ev->tipo }}</td>
                    <td class="v-mini">{{ $ev->estacion->nombre ?? '—' }}</td>
                    <td class="v-mini">{{ $ev->detectado_en?->format('d/m/Y H:i') }}</td>
                    <td>{{ $ev->duracion_min ?? 0 }} min</td>
                    <td><x-ui.badge :valor="$ev->estado" /></td>
                </tr>
            @empty
                <tr><td colspan="6" class="vacio">No se registraron eventos en las estaciones del equipo.</td></tr>
            @endforelse
            </tbody></table></div>
    </x-ui.tarjeta>

    <div>
        <x-ui.tarjeta titulo="Salidas activas" icono="pausa" sin-relleno="1">
            <div class="v-lista">
                @forelse($salidas as $s)
                    <div class="v-lista__item">
                        <span class="v-avatar v-avatar--ambar">{{ $s->empleado?->iniciales() }}</span>
                        <div class="v-lista__txt"><strong>{{ $s->empleado->nombre_completo ?? '—' }}</strong><small>{{ $s->motivo }} · desde {{ $s->hora_salida?->format('H:i') }}</small></div>
                    </div>
                @empty
                    <div class="v-lista__item"><div class="v-lista__txt"><strong>Sin salidas activas</strong><small>Todo el equipo está en su puesto.</small></div></div>
                @endforelse
            </div>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Privacidad del monitoreo" icono="escudo">
            <div class="v-estado v-estado--info"><x-ui.icono name="escudo" :size="16" /><div><strong>Solo presencia</strong>El supervisor ve el evento y su duración, no imágenes del trabajador.</div></div>
            <div class="v-dato"><span>Identificación facial</span><strong>No utilizada</strong></div>
            <div class="v-dato"><span>Audio</span><strong>No capturado</strong></div>
            <div class="v-dato"><span>Uso del evento</span><strong>Revisión de RR. HH.</strong></div>
        </x-ui.tarjeta>
    </div>
</div>
@endsection
