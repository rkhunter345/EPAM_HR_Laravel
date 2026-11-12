@extends('layouts.app')
@section('titulo', 'Editar período')
@section('migas', 'Nómina')
@section('contenido')
<div class="cab"><div><h1>Editar período</h1><p>{{ $periodo->periodo }}</p></div>
<div class="cab__acciones"><a href="{{ route('periodos.show', $periodo) }}" class="btn">Ver boletas</a></div></div>
<form method="POST" action="{{ route('periodos.update', $periodo) }}">
    @csrf @method('PUT')
    <div class="card">
        <div class="seccion">
            <h2 class="seccion-titulo"><x-ui.icono name="planilla" :size="15" /> Cierre y estado</h2>
            <div class="form-grid">
                <div class="campo col-4"><label for="estado">Estado *</label>
                    <select id="estado" name="estado">@foreach(\App\Models\Nomina\PeriodoPlanilla::ESTADOS as $e)<option value="{{ $e }}" @selected(old('estado', $periodo->estado) === $e)>{{ $e }}</option>@endforeach</select></div>
                <div class="campo col-4"><label for="fecha_cierre">Fecha de cierre</label><input type="date" id="fecha_cierre" name="fecha_cierre" value="{{ old('fecha_cierre', optional($periodo->fecha_cierre)->format('Y-m-d')) }}"></div>
                <div class="campo col-4"><label for="fecha_pago">Fecha de pago</label><input type="date" id="fecha_pago" name="fecha_pago" value="{{ old('fecha_pago', optional($periodo->fecha_pago)->format('Y-m-d')) }}"></div>
                <div class="campo col-12"><label for="observacion">Observación</label><textarea id="observacion" name="observacion" rows="2">{{ old('observacion', $periodo->observacion) }}</textarea></div>
            </div>
        </div>
        <div class="form-acciones"><a href="{{ route('periodos.show', $periodo) }}" class="btn">Cancelar</a><button class="btn btn--pri">Guardar cambios</button></div>
    </div>
</form>
@endsection
