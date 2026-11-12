@extends('layouts.app')
@section('titulo', 'Vacaciones')
@section('migas', 'Asistencia')
@section('contenido')
<div class="cab">
    <div><h1>Vacaciones</h1><p>Solicitudes de descanso vacacional, licencias y permisos largos</p></div>
    <div class="cab__acciones"><a href="{{ route('vacaciones.create') }}" class="btn btn--pri"><x-ui.icono name="mas" :size="15" /> Nueva solicitud</a></div>
</div>
<div class="grid grid--3" style="margin-bottom:14px">
    <div class="kpi" style="margin:0"><div class="kpi__cabeza"><span class="kpi__icono tono-ambar"><x-ui.icono name="reloj" :size="13" /></span><span class="kpi__label">Solicitudes pendientes</span></div><div class="kpi__valor">{{ $pendientes }}</div></div>
    <div class="kpi" style="margin:0"><div class="kpi__cabeza"><span class="kpi__icono tono-verde"><x-ui.icono name="check" :size="13" /></span><span class="kpi__label">Días aprobados</span></div><div class="kpi__valor">{{ rtrim(rtrim(number_format($diasAprobados, 1), '0'), '.') }}</div></div>
    <div class="kpi" style="margin:0"><div class="kpi__cabeza"><span class="kpi__icono tono-azul"><x-ui.icono name="solicitud" :size="13" /></span><span class="kpi__label">Total de solicitudes</span></div><div class="kpi__valor">{{ $solicitudes->total() }}</div></div>
</div>
<div class="card">
    <form method="GET" action="{{ route('vacaciones.index') }}" class="filtros">
        <div class="campo"><select name="estado"><option value="">Todos los estados</option>
            @foreach(\App\Models\Asistencias\SolicitudVacaciones::ESTADOS as $estado)<option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $estado }}</option>@endforeach
        </select></div>
        <div class="campo"><select name="empleado_id"><option value="">Todos los empleados</option>
            @foreach($empleados as $id => $nombre)<option value="{{ $id }}" @selected(request('empleado_id') == $id)>{{ $nombre }}</option>@endforeach
        </select></div>
        <button class="btn"><x-ui.icono name="buscar" :size="14" /> Filtrar</button>
    </form>
    <div class="tabla-wrap">
        <table class="tabla">
            <thead><tr><th>Empleado</th><th>Tipo</th><th>Desde</th><th>Hasta</th><th>Días</th><th>Estado</th><th style="text-align:right">Acciones</th></tr></thead>
            <tbody>
            @forelse($solicitudes as $v)
                <tr>
                    <td><div class="celda-persona"><span class="avatar avatar--foto">{{ $v->empleado->iniciales() ?? '?' }}</span>
                        <div class="celda-persona__datos"><a href="{{ route('vacaciones.show', $v) }}"><strong>{{ $v->empleado->nombre_completo ?? '—' }}</strong></a>
                        <small>{{ $v->empleado->codigo ?? '' }}</small></div></div></td>
                    <td>{{ $v->tipo }}</td>
                    <td>{{ $v->desde->format('d/m/Y') }}</td>
                    <td>{{ $v->hasta->format('d/m/Y') }}</td>
                    <td>{{ rtrim(rtrim(number_format((float) $v->dias, 1), '0'), '.') }}</td>
                    <td><x-ui.badge :valor="$v->estado" /></td>
                    <td><div class="acciones">
                        @if($v->estado === 'Pendiente')
                            <form method="POST" action="{{ route('vacaciones.aprobar', $v) }}">@csrf<input type="hidden" name="estado" value="Aprobada"><button class="btn btn--sm">Aprobar</button></form>
                        @endif
                        <a class="btn btn--ico" href="{{ route('vacaciones.edit', $v) }}" title="Editar"><x-ui.icono name="lapiz" :size="15" /></a>
                        <form method="POST" action="{{ route('vacaciones.destroy', $v) }}" onsubmit="return confirm('¿Eliminar la solicitud?');">@csrf @method('DELETE')
                            <button class="btn btn--ico" title="Eliminar"><x-ui.icono name="basura" :size="15" /></button></form>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="7" class="vacio">No hay solicitudes de vacaciones.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card__pie"><small style="color:var(--texto-3)">{{ $solicitudes->total() }} solicitudes</small>{{ $solicitudes->links('components.crud.paginacion') }}</div>
</div>
@endsection
