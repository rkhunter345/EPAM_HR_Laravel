@extends('layouts.app')
@section('titulo', 'Justificaciones')
@section('migas', 'Asistencia')
@section('contenido')
<div class="cab">
    <div><h1>Justificaciones</h1><p>Revisión y aprobación de tardanzas, faltas y permisos justificados</p></div>
    <div class="cab__acciones">
        <a href="{{ route('justificaciones.create') }}" class="btn btn--pri"><x-ui.icono name="mas" :size="15" /> Registrar justificación</a>
    </div>
</div>

<div class="grid grid--4" style="margin-bottom:14px">
    <div class="kpi" style="margin:0"><div class="kpi__cabeza"><span class="kpi__icono tono-ambar"><x-ui.icono name="solicitud" :size="13" /></span><span class="kpi__label">Pendientes</span></div><div class="kpi__valor">{{ $pendientes }}</div></div>
    <div class="kpi" style="margin:0"><div class="kpi__cabeza"><span class="kpi__icono tono-verde"><x-ui.icono name="check" :size="13" /></span><span class="kpi__label">Aprobadas</span></div><div class="kpi__valor">{{ $aprobadas }}</div></div>
    <div class="kpi" style="margin:0"><div class="kpi__cabeza"><span class="kpi__icono tono-rojo"><x-ui.icono name="ausencia" :size="13" /></span><span class="kpi__label">Rechazadas</span></div><div class="kpi__valor">{{ $rechazadas }}</div></div>
    <div class="kpi" style="margin:0"><div class="kpi__cabeza"><span class="kpi__icono tono-azul"><x-ui.icono name="reporte" :size="13" /></span><span class="kpi__label">Total registradas</span></div><div class="kpi__valor">{{ $pendientes + $aprobadas + $rechazadas }}</div></div>
</div>

<div class="card">
    <form method="GET" action="{{ route('justificaciones.index') }}" class="filtros">
        <div class="campo">
            <select name="estado">
                <option value="">Todos los estados</option>
                @foreach(\App\Models\Asistencias\Justificacion::ESTADOS as $estado)
                    <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $estado }}</option>
                @endforeach
            </select>
        </div>
        <div class="campo">
            <select name="empleado_id">
                <option value="">Todos los empleados</option>
                @foreach($empleados as $id => $nombre)
                    <option value="{{ $id }}" @selected(request('empleado_id') == $id)>{{ $nombre }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn"><x-ui.icono name="buscar" :size="14" /> Filtrar</button>
    </form>

    <div class="tabla-wrap">
        <table class="tabla">
            <thead><tr><th>Empleado</th><th>Fecha</th><th>Motivo</th><th>Horario afectado</th><th>Estado</th><th>Revisado por</th><th style="text-align:right">Acciones</th></tr></thead>
            <tbody>
            @forelse($justificaciones as $j)
                <tr>
                    <td>
                        <div class="celda-persona">
                            <span class="avatar avatar--foto">{{ $j->empleado->iniciales() ?? '?' }}</span>
                            <div class="celda-persona__datos">
                                <a href="{{ route('justificaciones.show', $j) }}"><strong>{{ $j->empleado->nombre_completo ?? '—' }}</strong></a>
                                <small>{{ $j->empleado->codigo ?? '' }}</small>
                            </div>
                        </div>
                    </td>
                    <td>{{ $j->fecha->format('d/m/Y') }}</td>
                    <td>{{ $j->motivo }}</td>
                    <td>{{ $j->horario_afectado ?? '—' }}</td>
                    <td><x-ui.badge :valor="$j->estado" /></td>
                    <td>{{ $j->revisor->name ?? '—' }}</td>
                    <td>
                        <div class="acciones">
                            @if($j->estado === 'Pendiente')
                                <form method="POST" action="{{ route('justificaciones.aprobar', $j) }}">
                                    @csrf
                                    <input type="hidden" name="estado" value="Aprobada">
                                    <button class="btn btn--sm">Aprobar</button>
                                </form>
                            @endif
                            <a class="btn btn--ico" href="{{ route('justificaciones.edit', $j) }}" title="Editar"><x-ui.icono name="lapiz" :size="15" /></a>
                            <form method="POST" action="{{ route('justificaciones.destroy', $j) }}" onsubmit="return confirm('¿Eliminar la justificación?');">
                                @csrf @method('DELETE')
                                <button class="btn btn--ico" title="Eliminar"><x-ui.icono name="basura" :size="15" /></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="vacio">No hay justificaciones registradas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card__pie">
        <small style="color:var(--texto-3)">{{ $justificaciones->total() }} registros</small>
        {{ $justificaciones->links('components.crud.paginacion') }}
    </div>
</div>
@endsection
