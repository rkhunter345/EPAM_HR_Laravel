@extends('layouts.app')
@section('titulo', 'Nueva solicitud de vacaciones')
@section('migas', 'Asistencia')
@section('contenido')
<div class="cab"><div><h1>Nueva solicitud</h1><p>Los días se calculan automáticamente según el rango de fechas</p></div>
<div class="cab__acciones"><a href="{{ route('vacaciones.index') }}" class="btn">Volver</a></div></div>
<form method="POST" action="{{ route('vacaciones.store') }}">
    @csrf
    <div class="card">
        <div class="seccion">
            <h2 class="seccion-titulo"><x-ui.icono name="ausencia" :size="15" /> Datos de la solicitud</h2>
            <div class="form-grid">
                <div class="campo col-6"><label for="empleado_id">Empleado *</label>
                    <select id="empleado_id" name="empleado_id" required><option value="">Seleccionar</option>
                    @foreach($empleados as $id => $nombre)<option value="{{ $id }}" @selected(old('empleado_id') == $id)>{{ $nombre }}</option>@endforeach</select></div>
                <div class="campo col-6"><label for="tipo">Tipo *</label>
                    <select id="tipo" name="tipo">@foreach(\App\Models\Asistencias\SolicitudVacaciones::TIPOS as $t)<option value="{{ $t }}" @selected(old('tipo', 'Vacaciones') === $t)>{{ $t }}</option>@endforeach</select></div>
                <div class="campo col-6"><label for="desde">Desde *</label><input type="date" id="desde" name="desde" value="{{ old('desde', $solicitud->desde?->format('Y-m-d')) }}" required></div>
                <div class="campo col-6"><label for="hasta">Hasta *</label><input type="date" id="hasta" name="hasta" value="{{ old('hasta', $solicitud->hasta?->format('Y-m-d')) }}" required></div>
                <div class="campo col-6"><label for="estado">Estado *</label>
                    <select id="estado" name="estado">@foreach(\App\Models\Asistencias\SolicitudVacaciones::ESTADOS as $e)<option value="{{ $e }}" @selected(old('estado', 'Pendiente') === $e)>{{ $e }}</option>@endforeach</select></div>
                <div class="campo col-12"><label for="motivo">Motivo</label><textarea id="motivo" name="motivo" rows="2">{{ old('motivo') }}</textarea></div>
            </div>
        </div>
        <div class="form-acciones"><a href="{{ route('vacaciones.index') }}" class="btn">Cancelar</a><button class="btn btn--pri">Registrar solicitud</button></div>
    </div>
</form>
@endsection
