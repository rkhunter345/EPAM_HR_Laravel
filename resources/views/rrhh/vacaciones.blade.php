@extends('layouts.app')
@section('titulo', 'Vacaciones y permisos')
@section('seccion', 'Recursos Humanos')
@section('migas', 'Vacaciones y permisos')

@section('contenido')
<div class="v-cab">
    <div>
        <h1>Vacaciones y permisos</h1>
        <p>Gestión de solicitudes del personal. Ninguna decisión es automática: siempre la adopta una persona autorizada.</p>
    </div>
</div>

<div class="v-kpis">
    <x-ui.kpi label="Pendientes" :valor="$pendientes" icono="reloj" tono="ambar" sub="Solicitudes por decidir" />
    <x-ui.kpi label="Aprobadas" :valor="$aprobadas" icono="check" tono="verde" sub="Con goce de haber" />
    <x-ui.kpi label="Días otorgados" :valor="$diasOtorgados" icono="ausencia" tono="azul" sub="Acumulado histórico" />
    <x-ui.kpi label="Aprobadas del mes" :valor="$aprobadasMes" icono="calendario" tono="violeta" sub="Inician este mes" />
</div>

<x-ui.tarjeta titulo="Solicitudes de vacaciones" icono="ausencia" sin-relleno="1">
    <div class="v-tabla-wrap"><table class="tabla">
        <thead><tr><th>Trabajador</th><th>Área</th><th>Desde</th><th>Hasta</th><th>Días</th><th>Tipo</th><th>Estado</th><th>Decisión</th></tr></thead>
        <tbody>
        @forelse($vacaciones as $v)
            <tr>
                <td><strong>{{ $v->empleado->nombre_completo ?? '—' }}</strong></td>
                <td class="v-mini">{{ $v->empleado->departamento->nombre ?? '—' }}</td>
                <td>{{ $v->desde?->format('d/m/Y') }}</td>
                <td>{{ $v->hasta?->format('d/m/Y') }}</td>
                <td>{{ number_format((float) $v->dias, 1) }}</td>
                <td class="v-mini">{{ $v->tipo }}</td>
                <td><x-ui.badge :valor="$v->estado" /></td>
                <td>
                    <form method="POST" action="{{ route('rrhh.revisar', ['vacaciones', $v->id]) }}" style="display:flex;gap:4px;align-items:center">
                        @csrf
                        <select name="decision" style="padding:4px 6px;font-size:11px;border:1px solid #dfe5ee;border-radius:6px"><option>Aprobada</option><option>Rechazada</option><option>Información solicitada</option></select>
                        <input type="text" name="observacion" placeholder="Observación" style="padding:4px 6px;font-size:11px;border:1px solid #dfe5ee;border-radius:6px;width:105px">
                        <button class="btn btn--sm btn--pri">Guardar</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="vacio">No hay solicitudes de vacaciones.</td></tr>
        @endforelse
        </tbody></table></div>
</x-ui.tarjeta>

<x-ui.tarjeta titulo="Permisos por horas" icono="reloj" sin-relleno="1" class="v-mt">
    <div class="v-tabla-wrap"><table class="tabla">
        <thead><tr><th>Trabajador</th><th>Área</th><th>Fecha</th><th>Horario</th><th>Motivo</th><th>Estado</th><th>Decisión</th></tr></thead>
        <tbody>
        @forelse($permisos as $p)
            <tr>
                <td><strong>{{ $p->empleado->nombre_completo ?? '—' }}</strong></td>
                <td class="v-mini">{{ $p->empleado->departamento->nombre ?? '—' }}</td>
                <td>{{ $p->fecha?->format('d/m/Y') }}</td>
                <td>{{ substr((string) $p->hora_desde,0,5) }} – {{ substr((string) $p->hora_hasta,0,5) }}</td>
                <td class="v-mini">{{ \Illuminate\Support\Str::limit($p->motivo, 40) }}</td>
                <td><x-ui.badge :valor="$p->estado" /></td>
                <td>
                    <form method="POST" action="{{ route('rrhh.revisar', ['permiso', $p->id]) }}" style="display:flex;gap:4px;align-items:center">
                        @csrf
                        <select name="decision" style="padding:4px 6px;font-size:11px;border:1px solid #dfe5ee;border-radius:6px"><option>Aprobada</option><option>Rechazada</option><option>Información solicitada</option></select>
                        <input type="text" name="observacion" placeholder="Observación" style="padding:4px 6px;font-size:11px;border:1px solid #dfe5ee;border-radius:6px;width:105px">
                        <button class="btn btn--sm btn--pri">Guardar</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="vacio">No hay permisos registrados.</td></tr>
        @endforelse
        </tbody></table></div>
</x-ui.tarjeta>
@endsection
