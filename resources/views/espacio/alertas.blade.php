@extends('layouts.app')
@section('titulo', 'Mis alertas')
@section('seccion', 'Mi espacio')
@section('migas', 'Alertas')

@section('contenido')
<div class="v-cab">
    <div>
        <h1>Mis alertas</h1>
        <p>Notificaciones sobre tu jornada, refrigerios, justificaciones, boletas y eventos del monitor de presencia.</p>
    </div>
    <div class="v-cab__acc"><span class="v-pill is-active">{{ $alertas->count() }} alertas</span></div>
</div>

<x-ui.tarjeta titulo="Bandeja de alertas" icono="campana" nota="Los eventos IoT son indicios a revisar, nunca una sanción automática" sin-relleno="1">
    <div class="v-lista">
        @forelse($alertas as $a)
            <div class="v-lista__item">
                <span class="v-lista__ico tono-{{ $a['tono'] ?? 'azul' }}"><x-ui.icono :name="$a['icono'] ?? 'campana'" :size="14" /></span>
                <div class="v-lista__txt">
                    <strong>{{ $a['titulo'] }}</strong>
                    <small>{{ $a['detalle'] }}</small>
                </div>
                <span class="v-lista__hora">{{ $a['hora'] ?? '' }}</span>
                @isset($a['estado'])<x-ui.badge :valor="$a['estado']" />@endisset
            </div>
        @empty
            <div class="v-lista__item"><div class="v-lista__txt"><strong>Sin alertas</strong><small>No hay notificaciones pendientes para ti.</small></div></div>
        @endforelse
    </div>
</x-ui.tarjeta>

<div class="v-grid v-grid--3 v-mt">
    <x-ui.tarjeta titulo="Qué genera una alerta" icono="alerta">
        <div class="v-dato"><span>Refrigerio sin marcar</span><strong>Recordatorio</strong></div>
        <div class="v-dato"><span>Jornada abierta</span><strong>Aviso al cierre</strong></div>
        <div class="v-dato"><span>Tardanza registrada</span><strong>Permite justificar</strong></div>
        <div class="v-dato"><span>Ausencia detectada por IoT</span><strong>A revisión de RR. HH.</strong></div>
    </x-ui.tarjeta>
    <x-ui.tarjeta titulo="Tolerancia de ausencia" icono="reloj">
        <div class="v-dato"><span>Valor configurado</span><strong>{{ \App\Models\Sistema\Parametro::valor('tolerancia_ausencia', 5) }} min</strong></div>
        <div class="v-dato"><span>Salida temporal</span><strong>Pausa el monitoreo</strong></div>
        <div class="v-dato"><span>Retorno antes del límite</span><strong>No genera alerta</strong></div>
        <p class="v-nota v-mt">La tolerancia no es una obligación legal: es una regla interna configurable por la empresa.</p>
    </x-ui.tarjeta>
    <x-ui.tarjeta titulo="Acciones sugeridas" icono="check">
        <a class="btn btn--bloque v-mb" href="{{ route('espacio.justificaciones') }}"><x-ui.icono name="solicitud" :size="14" /> Presentar justificación</a>
        <a class="btn btn--bloque v-mb" href="{{ route('espacio.asistencia') }}"><x-ui.icono name="reloj" :size="14" /> Revisar mi jornada</a>
        <a class="btn btn--bloque" href="{{ route('espacio.boletas') }}"><x-ui.icono name="planilla" :size="14" /> Ver mis boletas</a>
    </x-ui.tarjeta>
</div>
@endsection
