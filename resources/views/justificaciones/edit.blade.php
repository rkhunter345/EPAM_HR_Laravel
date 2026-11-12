@extends('layouts.app')
@section('titulo', 'Editar justificación')
@section('migas', 'Asistencia')
@section('contenido')
<div class="cab"><div><h1>Editar justificación</h1><p>{{ $justificacion->empleado->nombre_completo ?? '' }}</p></div>
<div class="cab__acciones"><a href="{{ route('justificaciones.show', $justificacion) }}" class="btn">Ver detalle</a></div></div>
<form method="POST" action="{{ route('justificaciones.update', $justificacion) }}">
    @csrf @method('PUT')
    <div class="card">
        <div class="seccion">
            <h2 class="seccion-titulo"><x-ui.icono name="solicitud" :size="15" /> Datos de la justificación</h2>
            <div class="form-grid">
                <div class="campo col-6"><label for="empleado_id">Empleado *</label>
                    <select id="empleado_id" name="empleado_id" required>
                        @foreach($empleados as $id => $nombre)<option value="{{ $id }}" @selected(old('empleado_id', $justificacion->empleado_id) == $id)>{{ $nombre }}</option>@endforeach
                    </select></div>
                <div class="campo col-6"><label for="fecha">Fecha *</label><input type="date" id="fecha" name="fecha" value="{{ old('fecha', $justificacion->fecha?->format('Y-m-d')) }}" required></div>
                <div class="campo col-6"><label for="motivo">Motivo *</label><input type="text" id="motivo" name="motivo" value="{{ old('motivo', $justificacion->motivo) }}" required></div>
                <div class="campo col-6"><label for="horario_afectado">Horario afectado</label><input type="text" id="horario_afectado" name="horario_afectado" value="{{ old('horario_afectado', $justificacion->horario_afectado) }}"></div>
                <div class="campo col-6"><label for="comunicante">Comunicante</label><input type="text" id="comunicante" name="comunicante" value="{{ old('comunicante', $justificacion->comunicante) }}"></div>
                <div class="campo col-6"><label for="estado">Estado *</label>
                    <select id="estado" name="estado">@foreach(\App\Models\Asistencias\Justificacion::ESTADOS as $estado)<option value="{{ $estado }}" @selected(old('estado', $justificacion->estado) === $estado)>{{ $estado }}</option>@endforeach</select></div>
                <div class="campo col-12"><label for="explicacion">Explicación</label><textarea id="explicacion" name="explicacion" rows="3">{{ old('explicacion', $justificacion->explicacion) }}</textarea></div>
            </div>
        </div>
        <div class="form-acciones"><a href="{{ route('justificaciones.show', $justificacion) }}" class="btn">Cancelar</a><button class="btn btn--pri">Guardar cambios</button></div>
    </div>
</form>
@endsection
