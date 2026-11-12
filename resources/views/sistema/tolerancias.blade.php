@extends('layouts.app')
@section('titulo', 'Asistencia y tolerancias')
@section('seccion', 'Administración')
@section('migas', 'Asistencia y tolerancias')

@section('contenido')
<div class="v-cab">
    <div>
        <h1>Asistencia y tolerancias</h1>
        <p>Reglas técnicas del monitoreo y de la jornada. La tolerancia es una regla interna configurable, no una obligación legal.</p>
    </div>
</div>

<div class="v-kpis">
    <x-ui.kpi label="Tolerancia de ausencia" :valor="$tolerancia" unidad="min" icono="reloj" tono="ambar" sub="Valor inicial de cinco minutos" />
    <x-ui.kpi label="Monitoreo IoT" :valor="$monitoreo ? 'Activo' : 'Inactivo'" icono="camara" tono="{{ $monitoreo ? 'verde' : 'rojo' }}" sub="Detección de presencia" />
    <x-ui.kpi label="Estaciones" :valor="$estaciones->count()" icono="servidor" tono="azul" sub="Cámaras y puntos QR" />
    <x-ui.kpi label="Marcaciones hoy" :valor="$marcacionesHoy" icono="actividad" tono="violeta" sub="Registros del día" />
</div>

<div class="v-grid v-grid--lado">
    <div>
        <x-ui.tarjeta titulo="Parámetros de asistencia" icono="reloj" nota="Aplican a todas las estaciones y calendarios">
            <form method="POST" action="{{ route('sistema.parametros.guardar') }}">
                @csrf
                @forelse($parametros as $p)
                    <div class="campo">
                        <label>{{ $p->etiqueta }} <span class="v-mini">{{ $p->clave }}</span></label>
                        <input type="text" name="{{ $p->clave }}" value="{{ $p->valor }}">
                    </div>
                @empty
                    <div class="v-estado v-estado--neutro"><x-ui.icono name="config" :size="16" /><div>Sin parámetros de asistencia registrados.</div></div>
                @endforelse
                <button class="btn btn--pri"><x-ui.icono name="check" :size="14" /> Guardar</button>
            </form>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Calendarios de trabajo" icono="calendario" sin-relleno="1">
            <div class="v-tabla-wrap"><table class="tabla">
                <thead><tr><th>Calendario</th><th>Días</th><th>Entrada</th><th>Salida</th><th>Tolerancia</th><th>Refrigerio</th></tr></thead>
                <tbody>
                @forelse($calendarios as $c)
                    <tr>
                        <td><strong>{{ $c->nombre }}</strong></td>
                        <td class="v-mini">{{ $c->dias_laborables }}</td>
                        <td>{{ substr((string) $c->hora_entrada, 0, 5) }}</td>
                        <td>{{ substr((string) $c->hora_salida, 0, 5) }}</td>
                        <td>{{ $c->tolerancia_min }} min</td>
                        <td>{{ $c->refrigerio_min }} min</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="vacio">Sin calendarios registrados.</td></tr>
                @endforelse
                </tbody></table></div>
        </x-ui.tarjeta>
    </div>

    <div>
        <x-ui.tarjeta titulo="Estaciones IoT" icono="servidor" sin-relleno="1">
            <div class="v-lista">
                @forelse($estaciones as $es)
                    <div class="v-lista__item">
                        <span class="v-lista__ico tono-azul"><x-ui.icono :name="$es->tipo === 'qr' ? 'qr' : 'camara'" :size="14" /></span>
                        <div class="v-lista__txt"><strong>{{ $es->nombre }}</strong><small>{{ $es->ubicacion }} · último ping {{ $es->ultimo_ping?->format('d/m H:i') ?? 'sin datos' }}</small></div>
                        <x-ui.badge :valor="$es->activo ? 'Activo' : 'Inactivo'" />
                    </div>
                @empty
                    <div class="v-lista__item"><div class="v-lista__txt"><strong>Sin estaciones</strong><small>Registre una estación para recibir eventos.</small></div></div>
                @endforelse
            </div>
        </x-ui.tarjeta>
        <x-ui.tarjeta titulo="Criterios de configuración" icono="escudo">
            <div class="v-estado v-estado--info"><x-ui.icono name="escudo" :size="16" /><div><strong>Tolerancia configurable</strong>El valor inicial es de cinco minutos; el retorno antes del límite evita la alerta.</div></div>
            <div class="v-dato v-mt"><span>Salida temporal</span><strong>Pausa el monitoreo</strong></div>
            <div class="v-dato"><span>Tolerancia legal</span><strong>No aplica: es regla interna</strong></div>
        </x-ui.tarjeta>
    </div>
</div>
@endsection
