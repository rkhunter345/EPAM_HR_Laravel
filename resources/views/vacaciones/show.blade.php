@extends('layouts.app')
@section('titulo', 'Solicitud de vacaciones')
@section('migas', 'Asistencia')
@section('contenido')
<div class="cab">
    <div><h1>Solicitud de vacaciones</h1><p>{{ $solicitud->empleado->nombre_completo ?? '' }}</p></div>
    <div class="cab__acciones">
        <a href="{{ route('vacaciones.index') }}" class="btn">Volver</a>
        @if($solicitud->estado === 'Pendiente')
            <form method="POST" action="{{ route('vacaciones.aprobar', $solicitud) }}">@csrf<input type="hidden" name="estado" value="Aprobada">
                <button class="btn btn--pri"><x-ui.icono name="check" :size="14" /> Aprobar</button></form>
        @endif
    </div>
</div>
<div class="card">
    <div class="card__cuerpo">
        <dl class="detalle">
            <div class="detalle__item"><dt>Empleado</dt><dd>{{ $solicitud->empleado->nombre_completo ?? '—' }}</dd></div>
            <div class="detalle__item"><dt>Departamento</dt><dd>{{ $solicitud->empleado->departamento->nombre ?? '—' }}</dd></div>
            <div class="detalle__item"><dt>Tipo</dt><dd>{{ $solicitud->tipo }}</dd></div>
            <div class="detalle__item"><dt>Estado</dt><dd><x-ui.badge :valor="$solicitud->estado" /></dd></div>
            <div class="detalle__item"><dt>Desde</dt><dd>{{ $solicitud->desde->format('d/m/Y') }}</dd></div>
            <div class="detalle__item"><dt>Hasta</dt><dd>{{ $solicitud->hasta->format('d/m/Y') }}</dd></div>
            <div class="detalle__item"><dt>Días solicitados</dt><dd>{{ rtrim(rtrim(number_format((float) $solicitud->dias, 1), '0'), '.') }}</dd></div>
            <div class="detalle__item detalle__item--full"><dt>Motivo</dt><dd>{{ $solicitud->motivo ?? '—' }}</dd></div>
        </dl>
    </div>
</div>
@endsection
