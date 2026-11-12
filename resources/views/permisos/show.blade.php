@extends('layouts.app')
@section('titulo', 'Permiso')
@section('migas', 'Asistencia')
@section('contenido')
<div class="cab">
    <div><h1>Permiso</h1><p>{{ $permiso->empleado->nombre_completo ?? '' }} · {{ $permiso->fecha->format('d/m/Y') }}</p></div>
    <div class="cab__acciones">
        <a href="{{ route('permisos.index') }}" class="btn">Volver</a>
        @if($permiso->estado === 'Pendiente')
            <form method="POST" action="{{ route('permisos.aprobar', $permiso) }}">@csrf<input type="hidden" name="estado" value="Aprobada">
                <button class="btn btn--pri"><x-ui.icono name="check" :size="14" /> Aprobar</button></form>
        @endif
    </div>
</div>
<div class="card">
    <div class="card__cuerpo">
        <dl class="detalle">
            <div class="detalle__item"><dt>Empleado</dt><dd>{{ $permiso->empleado->nombre_completo ?? '—' }}</dd></div>
            <div class="detalle__item"><dt>Departamento</dt><dd>{{ $permiso->empleado->departamento->nombre ?? '—' }}</dd></div>
            <div class="detalle__item"><dt>Fecha</dt><dd>{{ $permiso->fecha->format('d/m/Y') }}</dd></div>
            <div class="detalle__item"><dt>Horario</dt><dd>{{ substr((string) $permiso->hora_desde, 0, 5) }} – {{ substr((string) $permiso->hora_hasta, 0, 5) }}</dd></div>
            <div class="detalle__item"><dt>Motivo</dt><dd>{{ $permiso->motivo }}</dd></div>
            <div class="detalle__item"><dt>Estado</dt><dd><x-ui.badge :valor="$permiso->estado" /></dd></div>
        </dl>
    </div>
</div>
@endsection
