@extends('layouts.app')
@section('titulo', 'Permisos')
@section('migas', 'Asistencia')
@section('contenido')
<div class="cab">
    <div><h1>Permisos</h1><p>Salidas y permisos por horas durante la jornada laboral</p></div>
    <div class="cab__acciones"><a href="{{ route('permisos.create') }}" class="btn btn--pri"><x-ui.icono name="mas" :size="15" /> Nuevo permiso</a></div>
</div>
<div class="card">
    <form method="GET" action="{{ route('permisos.index') }}" class="filtros">
        <div class="campo"><select name="estado"><option value="">Todos los estados</option>
            @foreach(\App\Models\Asistencias\SolicitudPermiso::ESTADOS as $e)<option value="{{ $e }}" @selected(request('estado') === $e)>{{ $e }}</option>@endforeach
        </select></div>
        <button class="btn"><x-ui.icono name="buscar" :size="14" /> Filtrar</button>
        <span style="margin-left:auto;font-size:11.5px;color:var(--texto-3)">Pendientes: {{ $pendientes }}</span>
    </form>
    <div class="tabla-wrap">
        <table class="tabla">
            <thead><tr><th>Empleado</th><th>Fecha</th><th>Horario</th><th>Motivo</th><th>Estado</th><th style="text-align:right">Acciones</th></tr></thead>
            <tbody>
            @forelse($permisos as $p)
                <tr>
                    <td><div class="celda-persona"><span class="avatar avatar--foto">{{ $p->empleado->iniciales() ?? '?' }}</span>
                        <div class="celda-persona__datos"><a href="{{ route('permisos.show', $p) }}"><strong>{{ $p->empleado->nombre_completo ?? '—' }}</strong></a>
                        <small>{{ $p->empleado->codigo ?? '' }}</small></div></div></td>
                    <td>{{ $p->fecha->format('d/m/Y') }}</td>
                    <td>{{ substr((string) $p->hora_desde, 0, 5) }} – {{ substr((string) $p->hora_hasta, 0, 5) }}</td>
                    <td>{{ $p->motivo }}</td>
                    <td><x-ui.badge :valor="$p->estado" /></td>
                    <td><div class="acciones">
                        @if($p->estado === 'Pendiente')
                            <form method="POST" action="{{ route('permisos.aprobar', $p) }}">@csrf<input type="hidden" name="estado" value="Aprobada"><button class="btn btn--sm">Aprobar</button></form>
                        @endif
                        <a class="btn btn--ico" href="{{ route('permisos.edit', $p) }}" title="Editar"><x-ui.icono name="lapiz" :size="15" /></a>
                        <form method="POST" action="{{ route('permisos.destroy', $p) }}" onsubmit="return confirm('¿Eliminar el permiso?');">@csrf @method('DELETE')
                            <button class="btn btn--ico" title="Eliminar"><x-ui.icono name="basura" :size="15" /></button></form>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="6" class="vacio">No hay permisos registrados.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card__pie"><small style="color:var(--texto-3)">{{ $permisos->total() }} permisos</small>{{ $permisos->links('components.crud.paginacion') }}</div>
</div>
@endsection
