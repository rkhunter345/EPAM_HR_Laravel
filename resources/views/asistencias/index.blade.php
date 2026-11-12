@extends('layouts.app')
@section('titulo', 'Marcaciones')
@section('migas', 'Asistencia')
@section('contenido')
<div class="cab">
    <div><h1>Marcaciones</h1><p>Control diario de entrada, refrigerio y salida del personal</p></div>
    <div class="cab__acciones">
        <form method="POST" action="{{ route('asistencias.marcar') }}" style="display:flex;gap:8px;align-items:center">
            @csrf
            <div class="campo" style="min-width:190px">
                <select name="empleado_id" required>
                    <option value="">Marcar a…</option>
                    @foreach($empleados as $id => $nombre)
                        <option value="{{ $id }}">{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" name="tipo" value="entrada">
            <button class="btn btn--pri"><x-ui.icono name="reloj" :size="14" /> Entrada ahora</button>
        </form>
        <a href="{{ route('asistencias.create') }}" class="btn"><x-ui.icono name="mas" :size="14" /> Registro manual</a>
    </div>
</div>

<div class="grid grid--4" style="margin-bottom:14px">
    <div class="kpi" style="margin:0"><div class="kpi__cabeza"><span class="kpi__icono tono-verde"><x-ui.icono name="check" :size="13" /></span><span class="kpi__label">Asistencias</span></div><div class="kpi__valor">{{ $resumen['asistio'] }}</div></div>
    <div class="kpi" style="margin:0"><div class="kpi__cabeza"><span class="kpi__icono tono-ambar"><x-ui.icono name="reloj" :size="13" /></span><span class="kpi__label">Tardanzas</span></div><div class="kpi__valor">{{ $resumen['tardanza'] }}</div></div>
    <div class="kpi" style="margin:0"><div class="kpi__cabeza"><span class="kpi__icono tono-rojo"><x-ui.icono name="ausencia" :size="13" /></span><span class="kpi__label">Ausencias</span></div><div class="kpi__valor">{{ $resumen['ausente'] }}</div></div>
    <div class="kpi" style="margin:0"><div class="kpi__cabeza"><span class="kpi__icono tono-azul"><x-ui.icono name="reporte" :size="13" /></span><span class="kpi__label">Puntualidad</span></div><div class="kpi__valor">{{ $puntualidad }} %</div></div>
</div>

<div class="card">
    <form method="GET" action="{{ route('asistencias.index') }}" class="filtros">
        <div class="campo">
            <select name="empleado_id">
                <option value="">Todos los empleados</option>
                @foreach($empleados as $id => $nombre)
                    <option value="{{ $id }}" @selected(request('empleado_id') == $id)>{{ $nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="campo">
            <select name="estado">
                <option value="">Todos los estados</option>
                @foreach(\App\Models\Asistencias\Asistencia::ESTADOS as $estado)
                    <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $estado }}</option>
                @endforeach
            </select>
        </div>
        <div class="campo"><input type="date" name="desde" value="{{ $desde }}" title="Desde"></div>
        <div class="campo"><input type="date" name="hasta" value="{{ $hasta }}" title="Hasta"></div>
        <button class="btn"><x-ui.icono name="buscar" :size="14" /> Filtrar</button>
    </form>

    <div class="tabla-wrap">
        <table class="tabla">
            <thead><tr>
                <th>Empleado</th><th>Fecha</th><th>Entrada</th><th>Refrigerio</th><th>Salida</th>
                <th>Tardanza</th><th>Horas</th><th>Origen</th><th>Estado</th><th style="text-align:right">Acciones</th>
            </tr></thead>
            <tbody>
            @forelse($asistencias as $a)
                <tr>
                    <td>
                        <div class="celda-persona">
                            <span class="avatar avatar--foto">{{ $a->empleado->iniciales() ?? '?' }}</span>
                            <div class="celda-persona__datos">
                                <a href="{{ route('empleados.show', $a->empleado) }}"><strong>{{ $a->empleado->nombre_completo ?? '—' }}</strong></a>
                                <small>{{ $a->empleado->codigo ?? '' }}</small>
                            </div>
                        </div>
                    </td>
                    <td>{{ $a->fecha->format('d/m/Y') }}</td>
                    <td>{{ $a->hora_entrada ?? '—' }}</td>
                    <td>{{ $a->hora_inicio_refrigerio ? $a->hora_inicio_refrigerio.' – '.($a->hora_fin_refrigerio ?? '…') : '—' }}</td>
                    <td>{{ $a->hora_salida ?? '—' }}</td>
                    <td>{{ $a->tardanza_min ? $a->tardanza_min.' min' : '—' }}</td>
                    <td>{{ $a->minutos_trabajados ? number_format($a->minutos_trabajados / 60, 2) : '—' }}</td>
                    <td>{{ $a->origen }}</td>
                    <td><x-ui.badge :valor="$a->estado" /></td>
                    <td>
                        <div class="acciones">
                            <a class="btn btn--ico" href="{{ route('asistencias.edit', $a) }}" title="Editar"><x-ui.icono name="lapiz" :size="15" /></a>
                            <form method="POST" action="{{ route('asistencias.destroy', $a) }}" onsubmit="return confirm('¿Eliminar esta marcación?');">
                                @csrf @method('DELETE')
                                <button class="btn btn--ico" title="Eliminar"><x-ui.icono name="basura" :size="15" /></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="vacio">No hay marcaciones en el período seleccionado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card__pie">
        <small style="color:var(--texto-3)">{{ $asistencias->total() }} marcaciones · {{ $desde }} a {{ $hasta }}</small>
        {{ $asistencias->links('components.crud.paginacion') }}
    </div>
</div>
@endsection
