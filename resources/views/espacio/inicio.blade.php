@extends('layouts.app')
@section('titulo', 'Mi espacio de trabajo')
@section('seccion', 'Mi espacio')
@section('migas', 'Inicio')

@section('contenido')
@php
    $u = auth()->user();
    $primero = explode(' ', trim($empleado->primer_nombre ?: $empleado->nombre_completo))[0];
    $h = fn ($t) => $t ? substr($t, 0, 5) : '—:—';
@endphp

<div class="v-hero">
    <h2>Buenos días, {{ $primero }}</h2>
    <p>Tu asistencia está al día. Recuerda marcar tu jornada, revisar tus alertas y consultar tus boletas cuando estén disponibles.</p>
    <div class="v-hero__tags">
        <span class="v-hero__tag"><x-ui.icono name="reloj" :size="12" /> Jornada: <strong>{{ $h($asistencia->hora_entrada ?? null) }}</strong></span>
        <span class="v-hero__tag"><x-ui.icono name="check" :size="12" /> Estado: <strong>{{ $siguiente['etapa'] ?? 'Jornada cerrada' }}</strong></span>
        <span class="v-hero__tag"><x-ui.icono name="calendario" :size="12" /> Días trabajados: <strong>{{ $resumen['dias'] }}</strong></span>
        <span class="v-hero__tag"><x-ui.icono name="ausencia" :size="12" /> Vacaciones: <strong>{{ $diasVacaciones }} días</strong></span>
    </div>
</div>

<div class="v-kpis">
    <x-ui.kpi label="Hora de entrada" :valor="$h($asistencia->hora_entrada ?? null)" icono="reloj" tono="verde" sub="Marcación registrada hoy" />
    <x-ui.kpi label="Horas trabajadas (mes)" :valor="round($resumen['minutos'] / 60, 1)" unidad="h" icono="actividad" tono="azul"
        :sub="$resumen['dias'].' días registrados'" />
    <x-ui.kpi label="Tardanzas del mes" :valor="$resumen['tardanzas']" icono="ausencia" tono="ambar" sub="Tolerancia aplicada según política" />
    <x-ui.kpi label="Salida estimada" :valor="substr($jornada->hora_salida ?? '17:00:00', 0, 5)" icono="salir" tono="violeta"
        :sub="$jornada->nombre ?? 'Jornada EPAM'" />
</div>

<div class="v-grid v-grid--lado">
    <div>
        <x-ui.tarjeta titulo="Marcación rápida" icono="reloj" nota="Registra tu jornada con un clic. El sistema calcula tardanza, refrigerio y horas efectivas.">
            @if($siguiente)
                <form method="POST" action="{{ route('espacio.marcar') }}" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                    @csrf
                    <input type="hidden" name="tipo" value="{{ $siguiente['tipo'] }}">
                    <div style="flex:1;min-width:220px">
                        <strong style="font-size:14px">{{ $siguiente['etapa'] }}</strong>
                        <div class="v-mini">Hora del servidor: {{ now()->format('H:i') }} · Origen: Web</div>
                    </div>
                    <button class="btn btn--pri"><x-ui.icono name="check" :size="14" /> {{ $siguiente['label'] }}</button>
                </form>
            @else
                <div class="v-estado v-estado--ok"><x-ui.icono name="check" :size="16" />
                    <div><strong>Jornada completada</strong>Registraste entrada, refrigerio, retorno y salida.</div>
                </div>
            @endif

            <div class="v-sep"></div>
            <div class="v-dato-grupo">
                <div class="v-dato-caja"><span>Entrada</span><strong>{{ $h($asistencia->hora_entrada ?? null) }}</strong></div>
                <div class="v-dato-caja"><span>Refrigerio</span><strong>{{ $h($asistencia->hora_inicio_refrigerio ?? null) }} – {{ $h($asistencia->hora_fin_refrigerio ?? null) }}</strong></div>
                <div class="v-dato-caja"><span>Salida</span><strong>{{ $h($asistencia->hora_salida ?? null) }}</strong></div>
            </div>

            @if($salida)
                <div class="v-estado v-estado--info v-mt"><x-ui.icono name="pausa" :size="16" />
                    <div><strong>Monitoreo pausado</strong>Salida temporal registrada a las {{ $salida->hora_salida?->format('H:i') }} ({{ $salida->motivo }}). Marca tu retorno al volver al puesto.</div>
                </div>
                <form method="POST" action="{{ route('salidas.regresar', $salida) }}" class="v-mt-s">@csrf
                    <button class="btn btn--pri btn--sm"><x-ui.icono name="play" :size="13" /> Registrar mi retorno</button>
                </form>
            @else
                <form method="POST" action="{{ route('salidas.store') }}" class="v-mt-s" style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end">@csrf
                    <div class="campo" style="flex:1;min-width:180px">
                        <label>Motivo de salida temporal</label>
                        <select name="motivo">
                            @foreach(\App\Models\Asistencias\SalidaTemporal::MOTIVOS as $m)<option>{{ $m }}</option>@endforeach
                        </select>
                    </div>
                    <button class="btn"><x-ui.icono name="pausa" :size="13" /> Registrar salida temporal</button>
                </form>
            @endif
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Actividad de hoy" icono="actividad" nota="Secuencia de eventos registrados en tu jornada">
            <div class="v-tl">
                <div class="v-tl__item {{ $asistencia?->hora_entrada ? 'is-hecho' : 'is-pendiente' }}">
                    <div class="v-tl__hora">{{ $h($asistencia->hora_entrada ?? null) }} · Entrada registrada</div>
                    <div class="v-tl__txt">{{ $asistencia?->origen ?? 'Web' }} · {{ ($asistencia->tardanza_min ?? 0) > 0 ? 'Tardanza de '.$asistencia->tardanza_min.' min' : 'Dentro de la tolerancia' }}</div>
                </div>
                <div class="v-tl__item {{ $asistencia?->hora_inicio_refrigerio ? 'is-hecho' : 'is-activo' }}">
                    <div class="v-tl__hora">{{ $h($asistencia->hora_inicio_refrigerio ?? null) }} · Inicio de refrigerio</div>
                    <div class="v-tl__txt">Refrigerio configurado: {{ $jornada->refrigerio_min ?? 45 }} minutos</div>
                </div>
                <div class="v-tl__item {{ $asistencia?->hora_fin_refrigerio ? 'is-hecho' : 'is-pendiente' }}">
                    <div class="v-tl__hora">{{ $h($asistencia->hora_fin_refrigerio ?? null) }} · Retorno del refrigerio</div>
                    <div class="v-tl__txt">Exceso: {{ $asistencia->exceso_refrigerio_min ?? 0 }} min</div>
                </div>
                <div class="v-tl__item {{ $asistencia?->hora_salida ? 'is-hecho' : 'is-pendiente' }}">
                    <div class="v-tl__hora">{{ $h($asistencia->hora_salida ?? null) }} · Salida de la jornada</div>
                    <div class="v-tl__txt">Horas efectivas: {{ number_format(($asistencia->minutos_trabajados ?? 0) / 60, 1) }} h</div>
                </div>
            </div>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Mi historial reciente" icono="calendario" nota="Últimos registros de asistencia" sin-relleno="1">
            <div class="v-tabla-wrap">
                <table class="tabla">
                    <thead><tr><th>Fecha</th><th>Entrada</th><th>Refrigerio</th><th>Salida</th><th>Horas</th><th>Estado</th></tr></thead>
                    <tbody>
                    @forelse($historial as $f)
                        <tr>
                            <td><strong>{{ $f->fecha->locale('es')->translatedFormat('d M Y') }}</strong><br><small class="v-mini">{{ $f->fecha->locale('es')->translatedFormat('l') }}</small></td>
                            <td>{{ $h($f->hora_entrada) }}</td>
                            <td>{{ $h($f->hora_inicio_refrigerio) }} – {{ $h($f->hora_fin_refrigerio) }}</td>
                            <td>{{ $h($f->hora_salida) }}</td>
                            <td>{{ number_format($f->minutos_trabajados / 60, 1) }} h</td>
                            <td><x-ui.badge :valor="$f->estado" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="vacio">Aún no hay marcaciones registradas.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.tarjeta>
    </div>

    <div>
        <x-ui.tarjeta titulo="Alertas recientes" icono="campana" nota="Notificaciones sobre tu jornada">
            <div class="v-lista" style="margin:-16px">
                @forelse($alertas as $a)
                    <div class="v-lista__item">
                        <span class="v-lista__ico tono-{{ $a['tono'] }}"><x-ui.icono :name="$a['icono']" :size="14" /></span>
                        <div class="v-lista__txt">
                            <strong>{{ $a['titulo'] }}</strong>
                            <small>{{ \Illuminate\Support\Str::limit($a['detalle'], 70) }}</small>
                        </div>
                        <span class="v-lista__hora">{{ $a['hora'] }}</span>
                    </div>
                @empty
                    <div class="v-lista__item"><div class="v-lista__txt"><strong>Sin alertas</strong><small>Todo en orden por ahora.</small></div></div>
                @endforelse
            </div>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Estado del monitoreo" icono="camara" nota="Detección de presencia en tu puesto">
            <div class="v-cam">
                <div class="v-cam__rejilla"></div>
                <span class="v-cam__live">EN VIVO</span>
                <div class="v-cam__marco"><x-ui.icono name="usuario" :size="54" /></div>
                <div class="v-cam__meta"><span>{{ $estacion->nombre ?? 'estacion-1' }}</span><span>{{ now()->format('H:i:s') }}</span></div>
            </div>
            <div class="v-estado v-estado--ok v-mt"><x-ui.icono name="check" :size="16" />
                <div><strong>Presencia detectada</strong>El sistema solo verifica que haya una persona en la zona; no identifica rostros ni graba audio.</div>
            </div>
            <div class="v-dato"><span>Tolerancia de ausencia</span><strong>{{ \App\Models\Sistema\Parametro::valor('tolerancia_ausencia', 5) }} min</strong></div>
            <div class="v-dato"><span>Eventos registrados hoy</span><strong>{{ $eventos->count() }}</strong></div>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Mi boleta de pago" icono="planilla">
            @if($boleta)
                <div class="v-dato"><span>Periodo</span><strong>{{ $boleta->periodo->periodo ?? '—' }}</strong></div>
                <div class="v-dato"><span>Neto a pagar</span><strong>S/ {{ number_format($boleta->neto, 2) }}</strong></div>
                <a class="btn btn--pri btn--bloque v-mt" href="{{ route('espacio.boletas') }}"><x-ui.icono name="descargar" :size="14" /> Ver mis boletas</a>
            @else
                <div class="v-estado v-estado--neutro"><x-ui.icono name="nota" :size="16" /><div>Tu boleta se publicará al cerrar el periodo de nómina.</div></div>
            @endif
        </x-ui.tarjeta>
    </div>
</div>
@endsection
