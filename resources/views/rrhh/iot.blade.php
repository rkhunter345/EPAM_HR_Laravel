@extends('layouts.app')
@section('titulo', 'IoT y alertas de presencia')
@section('seccion', 'Recursos Humanos')
@section('migas', 'IoT y alertas')

@section('contenido')
<div class="v-cab">
    <div>
        <h1>Monitoreo IoT y alertas</h1>
        <p>Eventos de presencia y uso de celular enviados por las estaciones. Son evidencia revisable: nunca una sanción automática.</p>
    </div>
</div>

<div class="v-kpis">
    <x-ui.kpi label="Por revisar" :valor="$pendientes" icono="alerta" tono="ambar" sub="Requieren decisión humana" />
    <x-ui.kpi label="Registrados hoy" :valor="$registradosHoy" icono="actividad" tono="azul" sub="Eventos del día" />
    <x-ui.kpi label="Revisados" :valor="$revisados" icono="check" tono="verde" sub="Con responsable asignado" />
    <x-ui.kpi label="Estaciones activas" :valor="$estaciones->count()" icono="camara" tono="violeta" sub="Cámaras y puntos QR" />
</div>

<div class="v-grid v-grid--lado">
    <div>
        <x-ui.tarjeta titulo="Eventos detectados" icono="camara" nota="Decisión: revisado o descartado, siempre con observación" sin-relleno="1">
            <div class="v-tabla-wrap"><table class="tabla">
                <thead><tr><th>Trabajador</th><th>Evento</th><th>Estación</th><th>Detectado</th><th>Duración</th><th>Estado</th><th>Revisión</th></tr></thead>
                <tbody>
                @forelse($eventos as $ev)
                    <tr>
                        <td><strong>{{ $ev->empleado->nombre_completo ?? 'Sin asignar' }}</strong></td>
                        <td>{{ ['ausencia_puesto'=>'Ausencia del puesto','uso_celular'=>'Uso de celular','regreso'=>'Regreso','marcacion_qr'=>'Marcación QR'][$ev->tipo] ?? $ev->tipo }}</td>
                        <td class="v-mini">{{ $ev->estacion->nombre ?? '—' }}</td>
                        <td class="v-mini">{{ $ev->detectado_en?->format('d/m/Y H:i') }}</td>
                        <td>{{ $ev->duracion_min ?? 0 }} min</td>
                        <td><x-ui.badge :valor="$ev->estado" /></td>
                        <td>
                            @if($ev->estado === 'Registrado')
                                <form method="POST" action="{{ route('rrhh.revisar', ['iot', $ev->id]) }}" style="display:flex;gap:4px;align-items:center">
                                    @csrf
                                    <select name="decision" style="padding:4px 6px;font-size:11px;border:1px solid #dfe5ee;border-radius:6px"><option>Revisado</option><option>Rechazada</option></select>
                                    <input type="text" name="observacion" placeholder="Observación" style="padding:4px 6px;font-size:11px;border:1px solid #dfe5ee;border-radius:6px;width:100px">
                                    <button class="btn btn--sm btn--pri">Guardar</button>
                                </form>
                            @else
                                <span class="v-mini">{{ $ev->observacion ?: 'Revisado' }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="vacio">No se registraron eventos IoT.</td></tr>
                @endforelse
                </tbody></table></div>
        </x-ui.tarjeta>
    </div>

    <div>
        <x-ui.tarjeta titulo="Estaciones" icono="camara" sin-relleno="1">
            <div class="v-lista">
                @forelse($estaciones as $es)
                    <div class="v-lista__item">
                        <span class="v-lista__ico tono-azul"><x-ui.icono :name="$es->tipo === 'qr' ? 'qr' : 'camara'" :size="14" /></span>
                        <div class="v-lista__txt"><strong>{{ $es->nombre }}</strong><small>{{ $es->ubicacion ?? 'Sin ubicación' }} · {{ $es->empleado->nombre_completo ?? 'sin asignar' }}</small></div>
                        <x-ui.badge :valor="$es->activo ? 'Activo' : 'Inactivo'" />
                    </div>
                @empty
                    <div class="v-lista__item"><div class="v-lista__txt"><strong>Sin estaciones</strong><small>Registre una estación para recibir eventos.</small></div></div>
                @endforelse
            </div>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Salidas temporales activas" icono="pausa" sin-relleno="1">
            <div class="v-lista">
                @forelse($salidas as $s)
                    <div class="v-lista__item">
                        <span class="v-avatar v-avatar--ambar">{{ $s->empleado?->iniciales() }}</span>
                        <div class="v-lista__txt"><strong>{{ $s->empleado->nombre_completo ?? '—' }}</strong><small>{{ $s->motivo }} · {{ $s->hora_salida?->format('H:i') }}</small></div>
                    </div>
                @empty
                    <div class="v-lista__item"><div class="v-lista__txt"><strong>Sin salidas</strong><small>Nadie tiene el monitoreo pausado.</small></div></div>
                @endforelse
            </div>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Límites del monitoreo" icono="escudo">
            <div class="v-estado v-estado--info"><x-ui.icono name="escudo" :size="16" /><div><strong>Solo presencia</strong>Sin reconocimiento facial, sin audio, sin imágenes almacenadas.</div></div>
            <div class="v-dato v-mt"><span>Eventos de ausencia</span><strong>{{ $tolerancia }}</strong></div>
            <div class="v-dato"><span>Descartados</span><strong>{{ $descartados }}</strong></div>
            <p class="v-nota v-mt">Ningún evento descuenta remuneración por sí solo: requiere revisión y decisión registrada.</p>
        </x-ui.tarjeta>
    </div>
</div>
@endsection
