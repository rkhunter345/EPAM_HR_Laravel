@extends('layouts.app')
@section('titulo', 'Justificaciones')
@section('seccion', 'Recursos Humanos')
@section('migas', 'Justificaciones')

@section('contenido')
<div class="v-cab">
    <div>
        <h1>Justificaciones</h1>
        <p>Revisa los casos presentados por el personal. No puede resolver sus propios casos: debe derivarlos a otro responsable.</p>
    </div>
</div>

<div class="v-kpis">
    <x-ui.kpi label="Pendientes" :valor="$pendientes" icono="reloj" tono="ambar" sub="Esperan decisión" />
    <x-ui.kpi label="Aprobadas" :valor="$aprobadas" icono="check" tono="verde" sub="Con sustento aceptado" />
    <x-ui.kpi label="Rechazadas" :valor="$rechazadas" icono="ausencia" tono="rojo" sub="Registro original se mantiene" />
    <x-ui.kpi label="Total registrado" :valor="$justificaciones->count()" icono="solicitud" tono="azul" sub="Histórico" />
</div>

<x-ui.tarjeta titulo="Casos presentados" icono="solicitud" nota="Decisión con observación obligatoria queda auditada" sin-relleno="1">
    <div class="v-tabla-wrap"><table class="tabla">
        <thead><tr><th>Trabajador</th><th>Área</th><th>Fecha</th><th>Motivo</th><th>Horario</th><th>Sustento</th><th>Estado</th><th>Decisión</th></tr></thead>
        <tbody>
        @forelse($justificaciones as $j)
            <tr>
                <td><div class="v-persona"><span class="v-avatar">{{ $j->empleado?->iniciales() }}</span><div><strong>{{ $j->empleado->nombre_completo ?? '—' }}</strong></div></div></td>
                <td class="v-mini">{{ $j->empleado->departamento->nombre ?? '—' }}</td>
                <td class="v-mini">{{ $j->fecha?->format('d/m/Y') }}</td>
                <td class="v-mini">{{ \Illuminate\Support\Str::limit($j->motivo, 46) }}</td>
                <td class="v-mini">{{ $j->horario_afectado ?: '—' }}</td>
                <td class="v-mini">@if($j->documento)<a href="{{ route('rrhh.justificaciones.adjunto', $j) }}">Ver</a>@else —@endif</td>
                <td><x-ui.badge :valor="$j->estado" /></td>
                <td>
                    <form method="POST" action="{{ route('rrhh.revisar', ['justificacion', $j->id]) }}" style="display:flex;gap:4px;align-items:center">
                        @csrf
                        <select name="decision" style="padding:4px 6px;font-size:11px;border:1px solid #dfe5ee;border-radius:6px"><option>Aprobada</option><option>Rechazada</option><option>Información solicitada</option></select>
                        <input type="text" name="observacion" placeholder="Observación" style="padding:4px 6px;font-size:11px;border:1px solid #dfe5ee;border-radius:6px;width:105px">
                        <button class="btn btn--sm btn--pri">Guardar</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="vacio">No hay justificaciones registradas.</td></tr>
        @endforelse
        </tbody></table></div>
</x-ui.tarjeta>

<p class="v-nota v-mt"><x-ui.icono name="escudo" :size="13" /> Si el caso corresponde a su propio expediente, el sistema lo bloquea y debe derivarlo a otro responsable autorizado.</p>
@endsection
