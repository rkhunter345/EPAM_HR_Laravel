@extends('layouts.app')
@section('titulo', 'Nuevo período de planilla')
@section('migas', 'Nómina')
@section('contenido')
<div class="cab"><div><h1>Nuevo período de planilla</h1><p>Al guardar podrá calcular automáticamente las boletas del mes</p></div>
<div class="cab__acciones"><a href="{{ route('periodos.index') }}" class="btn">Volver</a></div></div>
<form method="POST" action="{{ route('periodos.store') }}">
    @csrf
    <div class="card">
        <div class="seccion">
            <h2 class="seccion-titulo"><x-ui.icono name="planilla" :size="15" /> Datos del período</h2>
            <div class="form-grid">
                <div class="campo col-6"><label for="mes">Mes *</label>
                    <select id="mes" name="mes">@for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected(old('mes', $periodo->mes) == $m)>{{ ucfirst(\Carbon\Carbon::create()->month($m)->locale('es')->translatedFormat('F')) }}</option>
                    @endfor</select></div>
                <div class="campo col-6"><label for="anio">Año *</label><input type="number" id="anio" name="anio" value="{{ old('anio', $periodo->anio) }}" required></div>
                <div class="campo col-6"><label for="estado">Estado *</label>
                    <select id="estado" name="estado">@foreach(\App\Models\Nomina\PeriodoPlanilla::ESTADOS as $e)<option value="{{ $e }}" @selected(old('estado', 'Borrador') === $e)>{{ $e }}</option>@endforeach</select></div>
                <div class="campo col-12"><label for="observacion">Observación</label><textarea id="observacion" name="observacion" rows="2">{{ old('observacion') }}</textarea></div>
            </div>
        </div>
        <div class="form-acciones"><a href="{{ route('periodos.index') }}" class="btn">Cancelar</a><button class="btn btn--pri">Crear período</button></div>
    </div>
</form>
@endsection
