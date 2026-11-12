@extends('layouts.app')
@section('titulo', 'Solicitudes del equipo')
@section('seccion', 'Supervisión')
@section('migas', 'Solicitudes del equipo')

@section('contenido')
<div class="v-cab">
    <div>
        <h1>Solicitudes y justificaciones del equipo</h1>
        <p>Revisa los casos de tu equipo. No puedes aprobar tus propios casos: en ese supuesto el caso se deriva a Recursos Humanos.</p>
    </div>
</div>

<div class="v-tabs v-mb">
    <a href="{{ route('equipo.solicitudes', ['tab' => 'justificaciones']) }}" class="v-tab {{ $pestana === 'justificaciones' ? 'is-active' : '' }}">Justificaciones ({{ $justificaciones->where('estado','Pendiente')->count() }})</a>
    <a href="{{ route('equipo.solicitudes', ['tab' => 'vacaciones']) }}" class="v-tab {{ $pestana === 'vacaciones' ? 'is-active' : '' }}">Vacaciones ({{ $vacaciones->where('estado','Pendiente')->count() }})</a>
    <a href="{{ route('equipo.solicitudes', ['tab' => 'permisos']) }}" class="v-tab {{ $pestana === 'permisos' ? 'is-active' : '' }}">Permisos ({{ $permisos->where('estado','Pendiente')->count() }})</a>
</div>

@if($pestana === 'vacaciones')
    <x-ui.tarjeta titulo="Solicitudes de vacaciones" icono="ausencia" sin-relleno="1">
        <div class="v-tabla-wrap"><table class="tabla">
            <thead><tr><th>Trabajador</th><th>Desde</th><th>Hasta</th><th>Días</th><th>Motivo</th><th>Estado</th><th>Decisión</th></tr></thead>
            <tbody>
            @forelse($vacaciones as $v)
                <tr>
                    <td><strong>{{ $v->empleado->nombre_completo ?? '—' }}</strong></td>
                    <td>{{ $v->desde?->format('d/m/Y') }}</td>
                    <td>{{ $v->hasta?->format('d/m/Y') }}</td>
                    <td>{{ number_format((float) $v->dias, 1) }}</td>
                    <td class="v-mini">{{ \Illuminate\Support\Str::limit($v->motivo, 60) }}</td>
                    <td><x-ui.badge :valor="$v->estado" /></td>
                    <td>@include('equipo._decision', ['tipo' => 'vacaciones', 'id' => $v->id, 'propio' => $empleadoPropio && $v->empleado_id === $empleadoPropio->id])</td>
                </tr>
            @empty
                <tr><td colspan="7" class="vacio">No hay solicitudes de vacaciones del equipo.</td></tr>
            @endforelse
            </tbody></table></div>
    </x-ui.tarjeta>
@elseif($pestana === 'permisos')
    <x-ui.tarjeta titulo="Permisos por horas" icono="reloj" sin-relleno="1">
        <div class="v-tabla-wrap"><table class="tabla">
            <thead><tr><th>Trabajador</th><th>Fecha</th><th>Horario</th><th>Motivo</th><th>Estado</th><th>Decisión</th></tr></thead>
            <tbody>
            @forelse($permisos as $p)
                <tr>
                    <td><strong>{{ $p->empleado->nombre_completo ?? '—' }}</strong></td>
                    <td>{{ $p->fecha?->format('d/m/Y') }}</td>
                    <td>{{ substr((string) $p->hora_desde,0,5) }} – {{ substr((string) $p->hora_hasta,0,5) }}</td>
                    <td class="v-mini">{{ \Illuminate\Support\Str::limit($p->motivo, 60) }}</td>
                    <td><x-ui.badge :valor="$p->estado" /></td>
                    <td>@include('equipo._decision', ['tipo' => 'permiso', 'id' => $p->id, 'propio' => $empleadoPropio && $p->empleado_id === $empleadoPropio->id])</td>
                </tr>
            @empty
                <tr><td colspan="6" class="vacio">No hay permisos registrados.</td></tr>
            @endforelse
            </tbody></table></div>
    </x-ui.tarjeta>
@else
    <x-ui.tarjeta titulo="Justificaciones del equipo" icono="solicitud" sin-relleno="1">
        <div class="v-tabla-wrap"><table class="tabla">
            <thead><tr><th>Trabajador</th><th>Fecha</th><th>Motivo</th><th>Horario</th><th>Estado</th><th>Decisión</th></tr></thead>
            <tbody>
            @forelse($justificaciones as $j)
                <tr>
                    <td><strong>{{ $j->empleado->nombre_completo ?? '—' }}</strong></td>
                    <td>{{ $j->fecha?->format('d/m/Y') }}</td>
                    <td class="v-mini">{{ \Illuminate\Support\Str::limit($j->motivo, 50) }}</td>
                    <td class="v-mini">{{ $j->horario_afectado ?: '—' }}</td>
                    <td><x-ui.badge :valor="$j->estado" /></td>
                    <td>@include('equipo._decision', ['tipo' => 'justificacion', 'id' => $j->id, 'propio' => $empleadoPropio && $j->empleado_id === $empleadoPropio->id])</td>
                </tr>
            @empty
                <tr><td colspan="6" class="vacio">No hay justificaciones registradas.</td></tr>
            @endforelse
            </tbody></table></div>
    </x-ui.tarjeta>
@endif

<div class="v-grid v-grid--2 v-mt">
    <x-ui.tarjeta titulo="Cómo se decide" icono="actividad">
        <div class="v-dato"><span>Aprobada</span><strong>Se acepta el caso y se refleja en el registro</strong></div>
        <div class="v-dato"><span>Rechazada</span><strong>Se mantiene el registro original con observación</strong></div>
        <div class="v-dato"><span>Información solicitada</span><strong>Se devuelve al trabajador para completar</strong></div>
    </x-ui.tarjeta>
    <x-ui.tarjeta titulo="Trazabilidad" icono="escudo">
        <div class="v-estado v-estado--info"><x-ui.icono name="escudo" :size="16" /><div><strong>Cada decisión queda auditada</strong>Se registra responsable, fecha, decisión y observación. El sistema nunca decide por sí mismo.</div></div>
    </x-ui.tarjeta>
</div>
@endsection
