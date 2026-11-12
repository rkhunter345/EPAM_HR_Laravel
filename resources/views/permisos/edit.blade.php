@extends('layouts.app')
@section('titulo', 'Editar permiso')
@section('migas', 'Asistencia')
@section('contenido')
<div class="cab"><div><h1>Editar permiso</h1><p>{{ $permiso->empleado->nombre_completo ?? '' }}</p></div>
<div class="cab__acciones"><a href="{{ route('permisos.show', $permiso) }}" class="btn">Ver detalle</a></div></div>
<form method="POST" action="{{ route('permisos.update', $permiso) }}">
    @csrf @method('PUT')
    <div class="card">
        <div class="seccion">
            <h2 class="seccion-titulo"><x-ui.icono name="check" :size="15" /> Datos del permiso</h2>
            <div class="form-grid">
                <div class="campo col-6"><label for="empleado_id">Empleado *</label>
                    <select id="empleado_id" name="empleado_id" required>
                    @foreach($empleados as $id => $nombre)<option value="{{ $id }}" @selected(old('empleado_id', $permiso->empleado_id) == $id)>{{ $nombre }}</option>@endforeach</select></div>
                <div class="campo col-6"><label for="fecha">Fecha *</label><input type="date" id="fecha" name="fecha" value="{{ old('fecha', $permiso->fecha?->format('Y-m-d')) }}" required></div>
                <div class="campo col-3"><label for="hora_desde">Desde</label><input type="time" id="hora_desde" name="hora_desde" value="{{ old('hora_desde', substr((string) $permiso->hora_desde, 0, 5)) }}"></div>
                <div class="campo col-3"><label for="hora_hasta">Hasta</label><input type="time" id="hora_hasta" name="hora_hasta" value="{{ old('hora_hasta', substr((string) $permiso->hora_hasta, 0, 5)) }}"></div>
                <div class="campo col-6"><label for="estado">Estado *</label>
                    <select id="estado" name="estado">@foreach(\App\Models\Asistencias\SolicitudPermiso::ESTADOS as $e)<option value="{{ $e }}" @selected(old('estado', $permiso->estado) === $e)>{{ $e }}</option>@endforeach</select></div>
                <div class="campo col-12"><label for="motivo">Motivo *</label><input type="text" id="motivo" name="motivo" value="{{ old('motivo', $permiso->motivo) }}" required></div>
            </div>
        </div>
        <div class="form-acciones"><a href="{{ route('permisos.show', $permiso) }}" class="btn">Cancelar</a><button class="btn btn--pri">Guardar cambios</button></div>
    </div>
</form>
@endsection
