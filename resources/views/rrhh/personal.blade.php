@extends('layouts.app')
@section('titulo', 'Personal')
@section('seccion', 'Recursos Humanos')
@section('migas', 'Personal')

@section('contenido')
<div class="v-cab">
    <div>
        <h1>Personal y expedientes</h1>
        <p>Consulta los expedientes del personal, su organización, contrato y situación laboral.</p>
    </div>
</div>

<form method="GET" class="v-card v-mb" style="padding:12px 16px">
    <div class="v-barra-filtros">
        <div class="campo" style="max-width:280px"><label>Buscar</label><input type="text" name="q" value="{{ request('q') }}" placeholder="Nombre, código o documento"></div>
        <div class="campo" style="max-width:220px"><label>Departamento</label>
            <select name="departamento_id"><option value="">Todos</option>
                @foreach($departamentos as $d)<option value="{{ $d->id }}" @selected(request('departamento_id') == $d->id)>{{ $d->nombre }}</option>@endforeach
            </select>
        </div>
        <button class="btn btn--pri btn--sm"><x-ui.icono name="buscar" :size="13" /> Filtrar</button>
    </div>
</form>

<div class="v-kpis">
    <x-ui.kpi label="Total de expedientes" :valor="$total" icono="empleados" tono="azul" sub="Registrados en el sistema" />
    <x-ui.kpi label="Activos" :valor="$activos" icono="check" tono="verde" sub="Con contrato vigente" />
    <x-ui.kpi label="En proceso" :valor="$proceso" icono="reloj" tono="ambar" sub="Alta pendiente" />
    <x-ui.kpi label="Inactivos / suspendidos" :valor="$inactivos" icono="ausencia" tono="rojo" sub="Sin actividad" />
</div>

<x-ui.tarjeta titulo="Expedientes" icono="empleados" nota="Datos organizacionales, contrato y estado" sin-relleno="1">
    <div class="v-tabla-wrap"><table class="tabla">
        <thead><tr><th>Trabajador</th><th>Código</th><th>Documento</th><th>Área / cargo</th><th>Ingreso</th><th>Contrato</th><th>Estado</th></tr></thead>
        <tbody>
        @forelse($empleados as $e)
            <tr>
                <td><div class="v-persona"><span class="v-avatar">{{ $e->iniciales() }}</span>
                    <div><strong>{{ $e->nombre_completo }}</strong><small>{{ $e->correo_personal ?: $e->telefono ?: '—' }}</small></div></div></td>
                <td class="v-mono">{{ $e->codigo }}</td>
                <td class="v-mini">{{ $e->tipo_documento ?? 'DNI' }} {{ $e->numero_documento ?? '—' }}</td>
                <td class="v-mini">{{ $e->departamento->nombre ?? '—' }}<br>{{ $e->designacion->nombre ?? '' }}</td>
                <td class="v-mini">{{ $e->fecha_ingreso?->format('d/m/Y') }}</td>
                <td class="v-mini">{{ $e->tipoContrato->nombre ?? '—' }}</td>
                <td><x-ui.badge :valor="$e->estado" /></td>
            </tr>
        @empty
            <tr><td colspan="7" class="vacio">No se encontraron expedientes con los filtros aplicados.</td></tr>
        @endforelse
        </tbody></table></div>
    @if($empleados->hasPages())
        <div style="padding:12px 16px">{{ $empleados->links() }}</div>
    @endif
</x-ui.tarjeta>

<p class="v-nota v-mt"><x-ui.icono name="escudo" :size="13" /> La consulta de remuneraciones requiere permiso específico de nómina y queda registrada en la auditoría.</p>
@endsection
