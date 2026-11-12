@extends('layouts.app')
@section('titulo', 'Registrar marcación')
@section('migas', 'Asistencia')
@section('contenido')
<div class="cab">
    <div><h1>Registrar marcación</h1><p>El sistema calcula la tardanza y las horas trabajadas al guardar</p></div>
    <div class="cab__acciones"><a href="{{ route('asistencias.index') }}" class="btn">Volver</a></div>
</div>

<form method="POST" action="{{ route('asistencias.store') }}">
    @csrf
    <div class="card">
        <div class="seccion">
            <h2 class="seccion-titulo"><x-ui.icono name="reloj" :size="15" /> Jornada del día</h2>
            <p class="seccion-nota">Si no registra el refrigerio, se toma el valor del calendario del empleado.</p>
            <div class="form-grid">
                <div class="campo col-6">
                    <label for="empleado_id">Empleado *</label>
                    <select id="empleado_id" name="empleado_id" required>
                        <option value="">Seleccionar</option>
                        @foreach($empleados as $id => $nombre)
                            <option value="{{ $id }}" @selected(old('empleado_id') == $id)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo col-6">
                    <label for="fecha">Fecha *</label>
                    <input type="date" id="fecha" name="fecha" value="{{ old('fecha', $asistencia->fecha?->format('Y-m-d')) }}" required>
                </div>
                <div class="campo col-3"><label for="hora_entrada">Entrada</label><input type="time" id="hora_entrada" name="hora_entrada" value="{{ old('hora_entrada') }}"></div>
                <div class="campo col-3"><label for="hora_inicio_refrigerio">Inicio refrigerio</label><input type="time" id="hora_inicio_refrigerio" name="hora_inicio_refrigerio" value="{{ old('hora_inicio_refrigerio') }}"></div>
                <div class="campo col-3"><label for="hora_fin_refrigerio">Fin refrigerio</label><input type="time" id="hora_fin_refrigerio" name="hora_fin_refrigerio" value="{{ old('hora_fin_refrigerio') }}"></div>
                <div class="campo col-3"><label for="hora_salida">Salida</label><input type="time" id="hora_salida" name="hora_salida" value="{{ old('hora_salida') }}"></div>
                <div class="campo col-6">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        @foreach(\App\Models\Asistencias\Asistencia::ESTADOS as $estado)
                            <option value="{{ $estado }}" @selected(old('estado', $asistencia->estado) === $estado)>{{ $estado }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo col-6">
                    <label for="origen">Origen de la marcación</label>
                    <select id="origen" name="origen">
                        @foreach(\App\Models\Asistencias\Asistencia::ORIGENES as $origen)
                            <option value="{{ $origen }}" @selected(old('origen', $asistencia->origen) === $origen)>{{ $origen }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo col-12">
                    <label for="observacion">Observación</label>
                    <textarea id="observacion" name="observacion" rows="2">{{ old('observacion') }}</textarea>
                </div>
            </div>
        </div>
        <div class="form-acciones">
            <a href="{{ route('asistencias.index') }}" class="btn">Cancelar</a>
            <button class="btn btn--pri">Guardar y calcular</button>
        </div>
    </div>
</form>
@endsection
