@extends('layouts.app')
@section('titulo', 'Justificación')
@section('migas', 'Asistencia')
@section('contenido')
<div class="cab">
    <div><h1>Justificación</h1><p>{{ $justificacion->empleado->nombre_completo ?? '' }} · {{ $justificacion->fecha->format('d/m/Y') }}</p></div>
    <div class="cab__acciones">
        <a href="{{ route('justificaciones.index') }}" class="btn">Volver</a>
        @if($justificacion->estado === 'Pendiente')
            <form method="POST" action="{{ route('justificaciones.aprobar', $justificacion) }}">
                @csrf <input type="hidden" name="estado" value="Rechazada">
                <button class="btn btn--peligro">Rechazar</button>
            </form>
            <form method="POST" action="{{ route('justificaciones.aprobar', $justificacion) }}">
                @csrf <input type="hidden" name="estado" value="Aprobada">
                <button class="btn btn--pri"><x-ui.icono name="check" :size="14" /> Aprobar</button>
            </form>
        @endif
    </div>
</div>
<div class="card">
    <div class="card__cuerpo">
        <dl class="detalle">
            <div class="detalle__item"><dt>Empleado</dt><dd>{{ $justificacion->empleado->nombre_completo ?? '—' }}</dd></div>
            <div class="detalle__item"><dt>Departamento</dt><dd>{{ $justificacion->empleado->departamento->nombre ?? '—' }}</dd></div>
            <div class="detalle__item"><dt>Fecha</dt><dd>{{ $justificacion->fecha->format('d/m/Y') }}</dd></div>
            <div class="detalle__item"><dt>Motivo</dt><dd>{{ $justificacion->motivo }}</dd></div>
            <div class="detalle__item"><dt>Horario afectado</dt><dd>{{ $justificacion->horario_afectado ?? '—' }}</dd></div>
            <div class="detalle__item"><dt>Estado</dt><dd><x-ui.badge :valor="$justificacion->estado" /></dd></div>
            <div class="detalle__item"><dt>Comunicante</dt><dd>{{ $justificacion->comunicante ?? '—' }}</dd></div>
            <div class="detalle__item"><dt>Revisado por</dt><dd>{{ $justificacion->revisor->name ?? '—' }}</dd></div>
            <div class="detalle__item detalle__item--full"><dt>Explicación</dt><dd>{{ $justificacion->explicacion ?? '—' }}</dd></div>
            <div class="detalle__item detalle__item--full"><dt>Observación de RR.HH.</dt><dd>{{ $justificacion->observacion ?? '—' }}</dd></div>
        </dl>
    </div>
    <div class="form-acciones">
        <a href="{{ route('justificaciones.edit', $justificacion) }}" class="btn btn--pri"><x-ui.icono name="lapiz" :size="14" /> Editar</a>
    </div>
</div>
@endsection
