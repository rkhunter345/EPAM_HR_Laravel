@extends('layouts.app')
@section('titulo', 'Asistencia y jornada laboral')
@section('seccion', 'Mi espacio')
@section('migas', 'Asistencia y jornada laboral')

@section('contenido')
@php $h = fn ($t) => $t ? substr($t, 0, 5) : '—:—'; @endphp
<div class="v-cab">
    <div>
        <h1>Asistencia y jornada laboral</h1>
        <p>Registra tu entrada y salida, controla el refrigerio y consulta el estado de tu jornada. Cada marcación queda con fecha, hora y origen.</p>
    </div>
    <div class="v-cab__acc">
        <a class="btn" href="{{ route('espacio.historial') }}"><x-ui.icono name="calendario" :size="14" /> Mi historial</a>
        <a class="btn" href="{{ route('espacio.monitoreo') }}"><x-ui.icono name="camara" :size="14" /> Monitoreo</a>
    </div>
</div>

<div class="v-card v-mb" style="padding:12px 16px">
    <div class="v-stepper">
        @php
            $pasos = [
                ['i' => 1, 't' => 'Entrada registrada', 'hecho' => (bool) $asistencia?->hora_entrada],
                ['i' => 2, 't' => 'Inicio de refrigerio', 'hecho' => (bool) $asistencia?->hora_inicio_refrigerio],
                ['i' => 3, 't' => 'Fin de refrigerio', 'hecho' => (bool) $asistencia?->hora_fin_refrigerio],
                ['i' => 4, 't' => 'Salida registrada', 'hecho' => (bool) $asistencia?->hora_salida],
            ];
        @endphp
        @foreach($pasos as $p)
            <span class="v-stepper__i {{ $p['hecho'] ? 'is-hecho' : (($siguiente['tipo'] ?? '') !== '' && !$p['hecho'] && $loop->first ? 'is-activo' : '') }}">
                <i>{{ $p['hecho'] ? '✓' : $p['i'] }}</i> {{ $p['t'] }}
            </span>
            @if(!$loop->last)<span class="v-stepper__sep">›</span>@endif
        @endforeach
    </div>
</div>

<div class="v-grid v-grid--lado">
    <div>
        <x-ui.tarjeta titulo="Registro de asistencia" icono="reloj" nota="La tolerancia y el refrigerio se aplican según el calendario asignado">
            <div class="v-dato-grupo" style="margin-bottom:14px">
                <div class="v-dato-caja"><span>Entrada</span><strong>{{ $h($asistencia->hora_entrada ?? null) }}</strong></div>
                <div class="v-dato-caja"><span>Salida</span><strong>{{ $h($asistencia->hora_salida ?? null) }}</strong></div>
                <div class="v-dato-caja"><span>Horas efectivas</span><strong>{{ number_format(($asistencia->minutos_trabajados ?? 0) / 60, 1) }} h</strong></div>
            </div>

            <div style="display:flex;gap:8px;flex-wrap:wrap">
                @foreach([
                    ['t' => 'entrada', 'l' => 'Marcar entrada', 'i' => 'reloj', 'on' => !$asistencia?->hora_entrada],
                    ['t' => 'refrigerio_inicio', 'l' => 'Iniciar refrigerio', 'i' => 'pausa', 'on' => $asistencia?->hora_entrada && !$asistencia?->hora_inicio_refrigerio],
                    ['t' => 'refrigerio_fin', 'l' => 'Terminar refrigerio', 'i' => 'play', 'on' => $asistencia?->hora_inicio_refrigerio && !$asistencia?->hora_fin_refrigerio],
                    ['t' => 'salida', 'l' => 'Marcar salida', 'i' => 'salir', 'on' => $asistencia?->hora_fin_refrigerio && !$asistencia?->hora_salida],
                ] as $b)
                    <form method="POST" action="{{ route('espacio.marcar') }}">@csrf
                        <input type="hidden" name="tipo" value="{{ $b['t'] }}">
                        <button class="btn {{ ($siguiente['tipo'] ?? '') === $b['t'] ? 'btn--pri' : '' }}" @disabled(!$b['on'])>
                            <x-ui.icono :name="$b['i']" :size="14" /> {{ $b['l'] }}
                        </button>
                    </form>
                @endforeach
            </div>

            <div class="v-sep"></div>
            <h3 class="seccion-titulo" style="font-size:12.5px"><x-ui.icono name="ausencia" :size="13" /> Salida temporal</h3>
            <p class="seccion-nota">Al registrar una salida temporal el monitoreo se pausa para no generar una alerta de ausencia.</p>
            @if($salida)
                <div class="v-estado v-estado--info"><x-ui.icono name="pausa" :size="16" />
                    <div><strong>Salida activa desde {{ $salida->hora_salida?->format('H:i') }}</strong>{{ $salida->motivo }} · tolerancia {{ $salida->tolerancia_min }} min</div>
                </div>
                <form method="POST" action="{{ route('salidas.regresar', $salida) }}" class="v-mt-s">@csrf
                    <button class="btn btn--pri"><x-ui.icono name="play" :size="14" /> Registrar retorno al puesto</button>
                </form>
            @else
                <form method="POST" action="{{ route('salidas.store') }}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end">@csrf
                    <div class="campo" style="flex:1;min-width:190px">
                        <label>Motivo</label>
                        <select name="motivo">@foreach(\App\Models\Asistencias\SalidaTemporal::MOTIVOS as $m)<option>{{ $m }}</option>@endforeach</select>
                    </div>
                    <div class="campo" style="flex:1;min-width:170px">
                        <label>Observación</label>
                        <input type="text" name="observacion" placeholder="Opcional">
                    </div>
                    <button class="btn"><x-ui.icono name="pausa" :size="14" /> Registrar salida temporal</button>
                </form>
            @endif

            <div class="v-sep"></div>
            <h3 class="seccion-titulo" style="font-size:12.5px"><x-ui.icono name="medico" :size="13" /> Reportar emergencia médica</h3>
            <p class="seccion-nota">La emergencia se reporta de inmediato, sin esperar el vencimiento del temporizador de ausencia.</p>
            <form method="POST" action="{{ route('espacio.emergencia') }}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end">@csrf
                <div class="campo" style="flex:1;min-width:180px"><label>Motivo</label><input type="text" name="motivo" value="Emergencia médica" required></div>
                <div class="campo" style="flex:1;min-width:150px"><label>Lugar</label><input type="text" name="lugar" placeholder="Piso / área"></div>
                <button class="btn btn--peligro"><x-ui.icono name="medico" :size="14" /> Reportar ahora</button>
            </form>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Últimas marcaciones" icono="calendario" sin-relleno="1">
            <div class="v-tabla-wrap"><table class="tabla">
                <thead><tr><th>Fecha</th><th>Entrada</th><th>Refrigerio</th><th>Salida</th><th>Tardanza</th><th>Estado</th></tr></thead>
                <tbody>
                @foreach($historial as $f)
                    <tr>
                        <td><strong>{{ $f->fecha->format('d/m/Y') }}</strong></td>
                        <td>{{ $h($f->hora_entrada) }}</td>
                        <td>{{ $h($f->hora_inicio_refrigerio) }}–{{ $h($f->hora_fin_refrigerio) }}</td>
                        <td>{{ $h($f->hora_salida) }}</td>
                        <td>{{ $f->tardanza_min }} min</td>
                        <td><x-ui.badge :valor="$f->estado" /></td>
                    </tr>
                @endforeach
                </tbody></table></div>
        </x-ui.tarjeta>
    </div>

    <div>
        <x-ui.tarjeta titulo="Estado de la jornada" icono="reloj">
            <div class="v-dato"><span>Calendario</span><strong>{{ $jornada->nombre ?? 'Jornada EPAM' }}</strong></div>
            <div class="v-dato"><span>Horario</span><strong>{{ substr($jornada->hora_entrada ?? '08:00', 0, 5) }} – {{ substr($jornada->hora_salida ?? '17:00', 0, 5) }}</strong></div>
            <div class="v-dato"><span>Tolerancia</span><strong>{{ $tolerancia }} min</strong></div>
            <div class="v-dato"><span>Refrigerio</span><strong>{{ $refrigerio }} min</strong></div>
            <div class="v-dato"><span>Etapa actual</span><strong>{{ $siguiente['etapa'] ?? 'Jornada cerrada' }}</strong></div>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Estado del monitor" icono="camara">
            <div class="v-cam">
                <div class="v-cam__rejilla"></div>
                @if($salida)<div class="v-cam__pausa"><div><strong>Monitoreo pausado</strong>Salida temporal autorizada</div></div>
                @else<span class="v-cam__live">EN VIVO</span>@endif
                <div class="v-cam__marco"><x-ui.icono name="usuario" :size="54" /></div>
                <div class="v-cam__meta"><span>{{ $estacion->nombre ?? 'estacion-1' }}</span><span>{{ now()->format('H:i:s') }}</span></div>
            </div>
            <div class="v-estado v-estado--ok v-mt"><x-ui.icono name="check" :size="16" />
                <div><strong>{{ $salida ? 'Salida registrada' : 'Presencia detectada' }}</strong>El sistema detecta presencia, no identifica rostros ni graba audio.</div>
            </div>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Eventos del monitor" icono="actividad" sin-relleno="1">
            <div class="v-lista">
                @forelse($eventos as $ev)
                    <div class="v-lista__item">
                        <span class="v-lista__ico tono-{{ $ev->tipo === 'uso_celular' ? 'ambar' : 'rojo' }}"><x-ui.icono name="camara" :size="14" /></span>
                        <div class="v-lista__txt">
                            <strong>{{ $ev->tipo === 'uso_celular' ? 'Uso de celular' : 'Ausencia del puesto' }}</strong>
                            <small>{{ $ev->detectado_en?->format('d/m H:i') }} · {{ $ev->duracion_min ?? 0 }} min · en revisión</small>
                        </div>
                        <x-ui.badge :valor="$ev->estado" />
                    </div>
                @empty
                    <div class="v-lista__item"><div class="v-lista__txt"><strong>Sin eventos</strong><small>No se registraron ausencias ni uso de celular.</small></div></div>
                @endforelse
            </div>
        </x-ui.tarjeta>
    </div>
</div>
@endsection
