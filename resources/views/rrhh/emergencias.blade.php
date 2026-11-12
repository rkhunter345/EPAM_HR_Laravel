@extends('layouts.app')
@section('titulo', 'Emergencias médicas')
@section('seccion', 'Recursos Humanos')
@section('migas', 'Emergencias médicas')

@section('contenido')
<div class="v-cab">
    <div>
        <h1>Emergencias médicas</h1>
        <p>Reportes inmediatos del personal. La emergencia no espera el temporizador de ausencia y se atiende sin generar descuentos automáticos.</p>
    </div>
</div>

<div class="v-kpis">
    <x-ui.kpi label="Pendientes" :valor="$pendientes" icono="medico" tono="rojo" sub="Atención inmediata" />
    <x-ui.kpi label="En revisión" :valor="$enRevision" icono="reloj" tono="ambar" sub="Con responsable asignado" />
    <x-ui.kpi label="Cerradas" :valor="$cerradas" icono="check" tono="verde" sub="Aprobadas o rechazadas" />
    <x-ui.kpi label="Del mes" :valor="$delMes" icono="calendario" tono="azul" sub="Reportes del mes actual" />
</div>

<x-ui.tarjeta titulo="Reportes de emergencia" icono="medico" nota="Cada caso conserva motivo, lugar, hora y responsable" sin-relleno="1">
    <div class="v-tabla-wrap"><table class="tabla">
        <thead><tr><th>Trabajador</th><th>Motivo</th><th>Lugar</th><th>Fecha y hora</th><th>Reportado por</th><th>Estado</th><th>Revisión</th></tr></thead>
        <tbody>
        @forelse($emergencias as $e)
            <tr>
                <td><strong>{{ $e->empleado->nombre_completo ?? '—' }}</strong></td>
                <td class="v-mini">{{ \Illuminate\Support\Str::limit($e->motivo, 46) }}</td>
                <td class="v-mini">{{ $e->lugar ?: '—' }}</td>
                <td class="v-mini">{{ $e->fecha_hora?->format('d/m/Y H:i') }}</td>
                <td class="v-mini">{{ $e->reportado_por ?: '—' }}</td>
                <td><x-ui.badge :valor="$e->estado" /></td>
                <td>
                    <form method="POST" action="{{ route('rrhh.revisar', ['emergencia', $e->id]) }}" style="display:flex;gap:4px;align-items:center">
                        @csrf
                        <select name="decision" style="padding:4px 6px;font-size:11px;border:1px solid #dfe5ee;border-radius:6px"><option>Aprobada</option><option>En revisión</option><option>Rechazada</option></select>
                        <input type="text" name="observacion" placeholder="Observación" style="padding:4px 6px;font-size:11px;border:1px solid #dfe5ee;border-radius:6px;width:105px">
                        <button class="btn btn--sm btn--pri">Guardar</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="vacio">No se registraron emergencias.</td></tr>
        @endforelse
        </tbody></table></div>
</x-ui.tarjeta>

<div class="v-grid v-grid--2 v-mt">
    <x-ui.tarjeta titulo="Procedimiento" icono="actividad">
        <div class="v-tl">
            <div class="v-tl__item is-hecho"><div class="v-tl__hora">1 · Reporte</div><div class="v-tl__txt">El trabajador reporta de inmediato, sin esperar la tolerancia.</div></div>
            <div class="v-tl__item is-activo"><div class="v-tl__hora">2 · Atención</div><div class="v-tl__txt">RR. HH. verifica y coordina la atención médica.</div></div>
            <div class="v-tl__item is-pendiente"><div class="v-tl__hora">3 · Cierre</div><div class="v-tl__txt">Se registra la decisión y, de corresponder, el sustento.</div></div>
        </div>
    </x-ui.tarjeta>
    <x-ui.tarjeta titulo="Protección de datos de salud" icono="escudo">
        <div class="v-estado v-estado--info"><x-ui.icono name="escudo" :size="16" /><div><strong>Acceso restringido</strong>Los datos de salud solo los consulta RR. HH. con permiso; el supervisor ve la ausencia, no el diagnóstico.</div></div>
    </x-ui.tarjeta>
</div>
@endsection
