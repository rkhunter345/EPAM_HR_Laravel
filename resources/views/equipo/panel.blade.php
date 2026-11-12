@extends('layouts.app')
@section('titulo', 'Panel de supervisión')
@section('seccion', 'Supervisión')
@section('migas', 'Panel de mi equipo')

@section('contenido')
@php
    $h = fn ($t) => $t ? substr($t, 0, 5) : '—:—';
    $dep = $miEmpleado?->departamento->nombre ?? 'Equipo asignado';
@endphp
<div class="v-cab">
    <div>
        <h1>Panel de supervisión</h1>
        <p>Vista de solo consulta del equipo de <strong>{{ $dep }}</strong>. Puedes revisar solicitudes, pero no aprobar tus propios casos.</p>
    </div>
    <div class="v-cab__acc">
        <a class="btn" href="{{ route('equipo.reportes') }}"><x-ui.icono name="reporte" :size="14" /> Reportes</a>
        <a class="btn btn--pri" href="{{ route('equipo.solicitudes') }}"><x-ui.icono name="solicitud" :size="14" /> Solicitudes ({{ $solicitudes }})</a>
    </div>
</div>

<div class="v-kpis">
    <x-ui.kpi label="Integrantes del equipo" :valor="$equipo->count()" icono="equipo" tono="azul" sub="{{ $presentes }} presentes hoy" />
    <x-ui.kpi label="Tardanzas de hoy" :valor="$tardanzas" icono="reloj" tono="ambar" sub="Fuera de la tolerancia" />
    <x-ui.kpi label="Ausencias" :valor="$ausentes" icono="ausencia" tono="rojo" sub="Sin marcación registrada" />
    <x-ui.kpi label="Eventos IoT pendientes" :valor="$alertasIot" icono="camara" tono="violeta" sub="Requieren revisión de RR. HH." />
</div>

<div class="v-grid v-grid--lado">
    <div>
        <x-ui.tarjeta titulo="Asistencia de hoy" icono="reloj" nota="Solo consulta: el supervisor no corrige marcaciones" sin-relleno="1">
            <div class="v-tabla-wrap"><table class="tabla">
                <thead><tr><th>Trabajador</th><th>Entrada</th><th>Salida</th><th>Horas</th><th>Estado</th></tr></thead>
                <tbody>
                @forelse($filas as $f)
                    <tr>
                        <td><div class="v-persona"><span class="v-avatar">{{ $f->empleado?->iniciales() }}</span>
                            <div><strong>{{ $f->empleado->nombre_completo ?? '—' }}</strong><small>{{ $f->empleado->designacion->nombre ?? '' }}</small></div></div></td>
                        <td>{{ $h($f->hora_entrada) }}</td>
                        <td>{{ $h($f->hora_salida) }}</td>
                        <td>{{ number_format($f->minutos_trabajados / 60, 1) }} h</td>
                        <td><x-ui.badge :valor="$f->estado" /></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="vacio">Aún no hay marcaciones del equipo hoy.</td></tr>
                @endforelse
                </tbody></table></div>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Alertas del equipo" icono="alerta" nota="Indicios a revisar; nunca una sanción automática">
            <div class="v-lista">
                @forelse($alertas as $a)
                    <div class="v-lista__item">
                        <span class="v-lista__ico tono-{{ $a['tono'] ?? 'azul' }}"><x-ui.icono :name="$a['icono'] ?? 'alerta'" :size="14" /></span>
                        <div class="v-lista__txt"><strong>{{ $a['titulo'] }}</strong><small>{{ \Illuminate\Support\Str::limit($a['detalle'] ?? '', 80) }}</small></div>
                        <span class="v-lista__hora">{{ $a['hora'] ?? '' }}</span>
                    </div>
                @empty
                    <div class="v-lista__item"><div class="v-lista__txt"><strong>Sin alertas</strong><small>El equipo no presenta incidencias hoy.</small></div></div>
                @endforelse
            </div>
        </x-ui.tarjeta>
    </div>

    <div>
        <x-ui.tarjeta titulo="Mi propia jornada" icono="usuario" nota="También tienes registros personales">
            @if($miAsistencia)
                <div class="v-dato"><span>Entrada</span><strong>{{ $h($miAsistencia->hora_entrada) }}</strong></div>
                <div class="v-dato"><span>Salida</span><strong>{{ $h($miAsistencia->hora_salida) }}</strong></div>
                <div class="v-dato"><span>Estado</span><strong>{{ $miAsistencia->estado }}</strong></div>
            @else
                <div class="v-estado v-estado--neutro"><x-ui.icono name="reloj" :size="16" /><div>Sin marcación registrada hoy.</div></div>
            @endif
            <a class="btn btn--bloque v-mt" href="{{ route('espacio.asistencia') }}"><x-ui.icono name="reloj" :size="14" /> Ir a mi marcación</a>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Miembros del equipo" icono="equipo" sin-relleno="1">
            <div class="v-lista">
                @foreach($equipo->take(8) as $e)
                    <div class="v-lista__item">
                        <span class="v-avatar">{{ $e->iniciales() }}</span>
                        <div class="v-lista__txt"><strong>{{ $e->nombre_completo }}</strong><small>{{ $e->designacion->nombre ?? $e->codigo }}</small></div>
                        <x-ui.badge :valor="$e->estado" />
                    </div>
                @endforeach
            </div>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Límites del rol supervisor" icono="escudo">
            <div class="v-estado v-estado--info"><x-ui.icono name="escudo" :size="16" /><div><strong>Sin acceso a nómina</strong>El supervisor no consulta remuneraciones ni edita marcaciones; solo revisa solicitudes de su equipo.</div></div>
            <p class="v-nota v-mt">No puede aprobar sus propios casos: si el afectado es el supervisor, el caso se deriva a Recursos Humanos.</p>
        </x-ui.tarjeta>
    </div>
</div>
@endsection
