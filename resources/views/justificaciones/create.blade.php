@extends('layouts.app')
@section('titulo', 'Nueva justificación')
@section('migas', 'Asistencia')
@section('contenido')
<div class="cab"><div><h1>Nueva justificación</h1><p>Registre el motivo y el horario afectado de la falta o tardanza</p></div>
<div class="cab__acciones"><a href="{{ route('justificaciones.index') }}" class="btn">Volver</a></div></div>
<form method="POST" action="{{ route('justificaciones.store') }}">
    @csrf
    <div class="card">
        <div class="seccion">
            <h2 class="seccion-titulo"><x-ui.icono name="solicitud" :size="15" /> Detalle de la justificación</h2>
            <div class="form-grid">
                <div class="campo col-6"><label for="empleado_id">Empleado *</label>
                    <select id="empleado_id" name="empleado_id" required><option value="">Seleccionar</option>
                        @foreach($empleados as $id => $nombre)<option value="{{ $id }}" @selected(old('empleado_id') == $id)>{{ $nombre }}</option>@endforeach
                    </select></div>
                <div class="campo col-6"><label for="fecha">Fecha *</label><input type="date" id="fecha" name="fecha" value="{{ old('fecha', $justificacion->fecha?->format('Y-m-d')) }}" required></div>
                <div class="campo col-6"><label for="motivo">Motivo *</label><input type="text" id="motivo" name="motivo" value="{{ old('motivo') }}" placeholder="Cita médica, trámite personal…" required></div>
                <div class="campo col-6"><label for="horario_afectado">Horario afectado</label><input type="text" id="horario_afectado" name="horario_afectado" value="{{ old('horario_afectado') }}" placeholder="Jornada completa / 08:00-10:00"></div>
                <div class="campo col-6"><label for="comunicante">Comunicante</label><input type="text" id="comunicante" name="comunicante" value="{{ old('comunicante') }}"></div>
                <div class="campo col-6"><label for="estado">Estado *</label>
                    <select id="estado" name="estado">@foreach(\App\Models\Asistencias\Justificacion::ESTADOS as $estado)<option value="{{ $estado }}" @selected(old('estado', 'Pendiente') === $estado)>{{ $estado }}</option>@endforeach</select></div>
                <div class="campo col-12"><label for="explicacion">Explicación</label><textarea id="explicacion" name="explicacion" rows="3">{{ old('explicacion') }}</textarea></div>
            </div>
        </div>
        <div class="form-acciones"><a href="{{ route('justificaciones.index') }}" class="btn">Cancelar</a><button class="btn btn--pri">Guardar justificación</button></div>
    </div>
</form>
@endsection
