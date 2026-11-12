@extends('layouts.app')
@section('titulo', 'Reportes')
@section('migas', 'Nómina')
@section('contenido')
<div class="cab">
    <div><h1>Reportes</h1><p>Generación de reportes de asistencia, tardanzas, horas trabajadas y planilla</p></div>
</div>

<div class="grid grid--lado">
    <div class="card" style="margin-top:0">
        <div class="card__cab"><div><h2>Generar reporte</h2><small>Seleccione el tipo y el rango de fechas</small></div></div>
        <form method="POST" action="{{ route('reportes.generar') }}">
            @csrf
            <div class="seccion">
                <div class="form-grid">
                    <div class="campo col-6">
                        <label for="tipo">Tipo de reporte *</label>
                        <select id="tipo" name="tipo" required>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo }}" @selected(old('tipo') === $tipo)>{{ $tipo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="campo col-6">
                        <label for="empleado_id">Empleado (opcional)</label>
                        <select id="empleado_id" name="empleado_id">
                            <option value="">Todos los empleados</option>
                            @foreach($empleados as $id => $nombre)
                                <option value="{{ $id }}" @selected(old('empleado_id') == $id)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="campo col-6"><label for="desde">Desde</label><input type="date" id="desde" name="desde" value="{{ old('desde', now()->startOfMonth()->toDateString()) }}"></div>
                    <div class="campo col-6"><label for="hasta">Hasta</label><input type="date" id="hasta" name="hasta" value="{{ old('hasta', now()->toDateString()) }}"></div>
                    <div class="campo col-12">
                        <label for="periodo_id">Período de planilla (para reportes de nómina)</label>
                        <select id="periodo_id" name="periodo_id">
                            <option value="">Último período</option>
                            @foreach($periodos as $id => $periodo)
                                <option value="{{ $id }}" @selected(old('periodo_id') == $id)>{{ $periodo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-acciones"><button class="btn btn--pri"><x-ui.icono name="reporte" :size="14" /> Generar reporte</button></div>
        </form>
    </div>

    <div class="card" style="margin-top:0">
        <div class="card__cab"><h2>Reportes recientes</h2></div>
        <div class="tabla-wrap">
            <table class="tabla">
                <thead><tr><th>Tipo</th><th>Empleado</th><th>Fecha</th><th></th></tr></thead>
                <tbody>
                @forelse($recientes as $r)
                    <tr>
                        <td><strong>{{ $r->tipo }}</strong></td>
                        <td>{{ $r->empleado->nombre_completo ?? 'Todos' }}</td>
                        <td>{{ $r->fecha->format('d/m/Y') }}</td>
                        <td style="text-align:right"><a class="btn btn--sm" href="{{ route('reportes.show', $r) }}">Ver</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="vacio">Todavía no se generaron reportes.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
