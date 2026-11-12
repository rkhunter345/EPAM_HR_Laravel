@extends('layouts.app')
@section('titulo', 'Monitoreo de presencia')
@section('seccion', 'Mi espacio')
@section('migas', 'Monitoreo de presencia')

@section('contenido')
<div class="v-cab">
    <div>
        <h1>Monitoreo de presencia (IoT)</h1>
        <p>Este módulo verifica únicamente que haya una persona en la zona de trabajo autorizada. No reconoce rostros ni graba audio.</p>
    </div>
    <div class="v-cab__acc">
        <span class="v-pill is-active"><x-ui.icono name="camara" :size="12" /> {{ $estacion->nombre ?? 'estacion-1' }}</span>
    </div>
</div>

<div class="v-kpis">
    <x-ui.kpi label="Estado del monitoreo" :valor="$monitoreoActivo ? 'Activo' : 'Pausado'" icono="camara" tono="{{ $monitoreoActivo ? 'verde' : 'ambar' }}" sub="{{ $salida ? 'Salida temporal registrada' : 'Dentro del horario laboral' }}" />
    <x-ui.kpi label="Tolerancia de ausencia" :valor="$tolerancia" unidad="min" icono="reloj" tono="azul" sub="Regla configurable de la empresa" />
    <x-ui.kpi label="Detección de celular" :valor="$deteccionCelular ? 'Activa' : 'Inactiva'" icono="celular" tono="violeta" sub="Evento a revisión, no sanción" />
    <x-ui.kpi label="Eventos registrados" :valor="$eventos->count()" icono="actividad" tono="ambar" sub="Últimos eventos del puesto" />
</div>

<div class="v-grid v-grid--lado">
    <x-ui.tarjeta titulo="Vista de la cámara del puesto" icono="camara" nota="Zona autorizada: {{ $estacion->ubicacion ?? 'Oficina principal' }}">
        <div class="v-cam">
            <div class="v-cam__rejilla"></div>
            @if($salida)
                <div class="v-cam__pausa"><div><strong>Monitoreo pausado</strong>Registraste una salida temporal a las {{ $salida->hora_salida?->format('H:i') }}.<br>El monitoreo se reanuda al marcar tu retorno.</div></div>
            @else
                <span class="v-cam__live">EN VIVO</span>
            @endif
            <div class="v-cam__marco"><x-ui.icono name="usuario" :size="64" /></div>
            <div class="v-cam__meta"><span>Cam {{ $estacion->nombre ?? 'estacion-1' }}</span><span>{{ now()->format('Y-m-d H:i:s') }}</span></div>
        </div>
        <div class="v-grid v-grid--2 v-mt">
            <div class="v-estado v-estado--ok"><x-ui.icono name="check" :size="16" /><div><strong>Presencia detectada</strong>Persona dentro del marco de la zona autorizada.</div></div>
            <div class="v-estado v-estado--warn"><x-ui.icono name="alerta" :size="16" /><div><strong>Ausencia no registrada</strong>Se inicia una tolerancia de {{ $tolerancia }} minutos antes de cualquier alerta.</div></div>
        </div>
        <p class="v-nota v-mt"><x-ui.icono name="escudo" :size="13" /> Si regresas antes de que termine la tolerancia, no se envía ninguna alerta. Una marca IoT nunca es una sanción automática: requiere revisión humana.</p>
    </x-ui.tarjeta>

    <div>
        <x-ui.tarjeta titulo="Historial de eventos" icono="actividad" sin-relleno="1">
            <div class="v-lista">
                @forelse($eventos as $ev)
                    <div class="v-lista__item">
                        <span class="v-lista__ico tono-{{ $ev->tipo === 'uso_celular' ? 'ambar' : 'rojo' }}"><x-ui.icono name="{{ $ev->tipo === 'uso_celular' ? 'celular' : 'camara' }}" :size="14" /></span>
                        <div class="v-lista__txt">
                            <strong>{{ ['ausencia_puesto'=>'Ausencia del puesto','uso_celular'=>'Uso de celular','regreso'=>'Regreso al puesto','marcacion_qr'=>'Marcación QR'][$ev->tipo] ?? $ev->tipo }}</strong>
                            <small>{{ $ev->detectado_en?->format('d/m/Y H:i') }} · {{ $ev->duracion_min ?? 0 }} min · {{ $ev->estacion->nombre ?? '' }}</small>
                        </div>
                        <x-ui.badge :valor="$ev->estado" />
                    </div>
                @empty
                    <div class="v-lista__item"><div class="v-lista__txt"><strong>Sin eventos</strong><small>Aún no hay detecciones en tu estación.</small></div></div>
                @endforelse
            </div>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Privacidad y límites" icono="escudo">
            <div class="v-estado v-estado--info v-mb"><x-ui.icono name="escudo" :size="16" /><div><strong>Solo detección de presencia</strong>No se guardan imágenes del trabajador ni se generan identificaciones biométricas.</div></div>
            <div class="v-dato"><span>Datos enviados</span><strong>Evento, hora y duración</strong></div>
            <div class="v-dato"><span>Audio</span><strong>No se captura</strong></div>
            <div class="v-dato"><span>Uso de la alerta</span><strong>Revisión por RR. HH.</strong></div>
        </x-ui.tarjeta>
    </div>
</div>
@endsection
