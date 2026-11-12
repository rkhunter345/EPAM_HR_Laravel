@extends('layouts.app')
@section('titulo', 'Nuevo permiso')
@section('migas', 'Asistencia')
@section('contenido')
<div class="cab"><div><h1>Nuevo permiso</h1><p>Salida autorizada dentro de la jornada laboral</p></div>
<div class="cab__acciones"><a href="{{ route('permisos.index') }}" class="btn">Volver</a></div></div>
<form method="POST" action="{{ route('permisos.store') }}">
    @csrf
    <div class="card">
        <div class="seccion">
            <h2 class="seccion-titulo"><x-ui.icono name="check" :size="15" /> Datos del permiso</h2>
            <div class="form-grid">
                <div class="campo col-6"><label for="empleado_id">Empleado *</label>
                    <select id="empleado_id" name="empleado_id" required><option value="">Seleccionar</option>
                    @foreach($empleados as $id => $nombre)<option value="{{ $id }}" @selected(old('empleado_id') == $id)>{{ $nombre }}</option>@endforeach</select></div>
                <div class="campo col-6"><label for="fecha">Fecha *</label><input type="date" id="fecha" name="fecha" value="{{ old('fecha', $permiso->fecha?->format('Y-m-d')) }}" required></div>
                <div class="campo col-3"><label for="hora_desde">Desde</label><input type="time" id="hora_desde" name="hora_desde" value="{{ old('hora_desde') }}"></div>
                <div class="campo col-3"><label for="hora_hasta">Hasta</label><input type="time" id="hora_hasta" name="hora_hasta" value="{{ old('hora_hasta') }}"></div>
                <div class="campo col-6"><label for="estado">Estado *</label>
                    <select id="estado" name="estado">@foreach(\App\Models\Asistencias\SolicitudPermiso::ESTADOS as $e)<option value="{{ $e }}" @selected(old('estado', 'Pendiente') === $e)>{{ $e }}</option>@endforeach</select></div>
                <div class="campo col-12"><label for="motivo">Motivo *</label><input type="text" id="motivo" name="motivo" value="{{ old('motivo') }}" required></div>
            </div>
        </div>
        <div class="form-acciones"><a href="{{ route('permisos.index') }}" class="btn">Cancelar</a><button class="btn btn--pri">Registrar permiso</button></div>
    </div>
</form>
@endsection
