@extends('layouts.app')
@section('titulo', 'Detalle de marcación')
@section('migas', 'Asistencia')
@section('contenido')
<div class="cab">
    <div><h1>Detalle de marcación</h1><p>{{ $asistencia->empleado->nombre_completo ?? '' }} · {{ $asistencia->fecha->format('d/m/Y') }}</p></div>
    <div class="cab__acciones">
        <a href="{{ route('asistencias.index') }}" class="btn">Volver</a>
        <a href="{{ route('asistencias.edit', $asistencia) }}" class="btn btn--pri">Editar</a>
    </div>
</div>
<div class="card">
    <div class="card__cuerpo">
        <dl class="detalle">
            <div class="detalle__item"><dt>Empleado</dt><dd>{{ $asistencia->empleado->nombre_completo ?? '—' }}</dd></div>
            <div class="detalle__item"><dt>Código</dt><dd>{{ $asistencia->empleado->codigo ?? '—' }}</dd></div>
            <div class="detalle__item"><dt>Departamento</dt><dd>{{ $asistencia->empleado->departamento->nombre ?? '—' }}</dd></div>
            <div class="detalle__item"><dt>Fecha</dt><dd>{{ $asistencia->fecha->format('d/m/Y') }}</dd></div>
            <div class="detalle__item"><dt>Entrada</dt><dd>{{ $asistencia->hora_entrada ?? '—' }}</dd></div>
            <div class="detalle__item"><dt>Salida</dt><dd>{{ $asistencia->hora_salida ?? '—' }}</dd></div>
            <div class="detalle__item"><dt>Refrigerio</dt><dd>{{ $asistencia->refrigerio_min }} min (exceso {{ $asistencia->exceso_refrigerio_min }} min)</dd></div>
            <div class="detalle__item"><dt>Tardanza</dt><dd>{{ $asistencia->tardanza_min }} min</dd></div>
            <div class="detalle__item"><dt>Minutos trabajados</dt><dd>{{ $asistencia->minutos_trabajados }} min ({{ number_format($asistencia->minutos_trabajados / 60, 2) }} h)</dd></div>
            <div class="detalle__item"><dt>Estado</dt><dd><x-ui.badge :valor="$asistencia->estado" /></dd></div>
            <div class="detalle__item"><dt>Origen</dt><dd>{{ $asistencia->origen }}</dd></div>
            <div class="detalle__item detalle__item--full"><dt>Observación</dt><dd>{{ $asistencia->observacion ?? '—' }}</dd></div>
        </dl>
    </div>
</div>
@endsection
