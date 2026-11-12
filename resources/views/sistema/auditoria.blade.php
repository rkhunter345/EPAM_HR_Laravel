@extends('layouts.app')
@section('titulo', 'Auditoría')
@section('seccion', 'Administración')
@section('migas', 'Auditoría')

@section('contenido')
<div class="v-cab">
    <div>
        <h1>Auditoría del sistema</h1>
        <p>Registro de solo lectura con usuario, rol, fecha, acción, módulo, registro afectado e IP cuando está disponible.</p>
    </div>
</div>

<form method="GET" class="v-card v-mb" style="padding:12px 16px">
    <div class="v-barra-filtros">
        <div class="campo" style="max-width:220px"><label>Módulo</label>
            <select name="modulo"><option value="">Todos</option>
                @foreach($modulos as $mo)<option value="{{ $mo }}" @selected(request('modulo') === $mo)>{{ $mo }}</option>@endforeach
            </select>
        </div>
        <div class="campo" style="max-width:240px"><label>Buscar detalle</label><input type="text" name="q" value="{{ request('q') }}" placeholder="Acción o detalle"></div>
        <button class="btn btn--pri btn--sm"><x-ui.icono name="filtrar" :size="13" /> Filtrar</button>
    </div>
</form>

<div class="v-kpis">
    <x-ui.kpi label="Eventos registrados" :valor="$total" icono="llave" tono="azul" sub="Histórico completo" />
    <x-ui.kpi label="Eventos de hoy" :valor="$hoy" icono="actividad" tono="verde" sub="Actividad del día" />
    <x-ui.kpi label="Usuarios con actividad" :valor="$usuarios" icono="usuario" tono="violeta" sub="Distintos responsables" />
    <x-ui.kpi label="Cambios sensibles" :valor="$cambios" icono="escudo" tono="ambar" sub="Permisos, parámetros y cuentas" />
</div>

<x-ui.tarjeta titulo="Registro de auditoría" icono="llave" nota="Los registros no se pueden editar ni eliminar desde la interfaz" sin-relleno="1">
    <div class="v-tabla-wrap"><table class="tabla">
        <thead><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Módulo</th><th>Detalle</th><th>IP</th></tr></thead>
        <tbody>
        @forelse($eventos as $e)
            <tr>
                <td class="v-mini">{{ $e->created_at?->format('d/m/Y H:i:s') }}</td>
                <td><strong>{{ $e->usuario ?? 'sistema' }}</strong></td>
                <td class="v-mini">{{ $e->accion }}</td>
                <td class="v-mini">{{ $e->modulo }}</td>
                <td class="v-mini">{{ \Illuminate\Support\Str::limit($e->detalle, 90) }}</td>
                <td class="v-mono v-mini">{{ $e->ip ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="vacio">Sin eventos de auditoría registrados.</td></tr>
        @endforelse
        </tbody></table></div>
    @if($eventos->hasPages())
        <div style="padding:12px 16px">{{ $eventos->links() }}</div>
    @endif
</x-ui.tarjeta>
@endsection
