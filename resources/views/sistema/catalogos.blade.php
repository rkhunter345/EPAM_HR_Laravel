@extends('layouts.app')
@section('titulo', 'Catálogos')
@section('seccion', 'Administración')
@section('migas', 'Catálogos')

@section('contenido')
<div class="v-cab">
    <div>
        <h1>Catálogos del sistema</h1>
        <p>Catálogos organizacionales y salariales. Al desactivar un registro se conserva su histórico para no romper los expedientes.</p>
    </div>
</div>

<div class="v-grid v-grid--2">
    <x-ui.tarjeta titulo="Compañías" icono="organigrama" :nota="$companias->count().' registradas'" sin-relleno="1">
        <div class="v-tabla-wrap"><table class="tabla">
            <thead><tr><th>Nombre</th><th>RUC</th><th>Moneda</th><th>Estado</th></tr></thead>
            <tbody>
            @foreach($companias as $c)
                <tr><td><strong>{{ $c->nombre }}</strong></td><td class="v-mini">{{ $c->ruc ?: '—' }}</td><td class="v-mini">{{ $c->moneda }}</td><td><x-ui.badge :valor="$c->activo ? 'Activo' : 'Inactivo'" /></td></tr>
            @endforeach
            </tbody></table></div>
    </x-ui.tarjeta>

    <x-ui.tarjeta titulo="Sucursales" icono="organigrama" :nota="$sucursales->count().' registradas'" sin-relleno="1">
        <div class="v-tabla-wrap"><table class="tabla">
            <thead><tr><th>Sucursal</th><th>Ciudad</th><th>Responsable</th></tr></thead>
            <tbody>
            @foreach($sucursales as $s)
                <tr><td><strong>{{ $s->nombre }}</strong></td><td class="v-mini">{{ $s->ciudad }}</td><td class="v-mini">{{ $s->responsable ?: '—' }}</td></tr>
            @endforeach
            </tbody></table></div>
    </x-ui.tarjeta>

    <x-ui.tarjeta titulo="Departamentos" icono="equipo" :nota="$departamentos->count().' áreas'" sin-relleno="1">
        <div class="v-tabla-wrap"><table class="tabla">
            <thead><tr><th>Departamento</th><th>Responsable</th><th>Colaboradores</th></tr></thead>
            <tbody>
            @foreach($departamentos as $d)
                <tr><td><strong>{{ $d->nombre }}</strong></td><td class="v-mini">{{ $d->responsable ?: '—' }}</td><td>{{ $d->empleados_count }}</td></tr>
            @endforeach
            </tbody></table></div>
    </x-ui.tarjeta>

    <x-ui.tarjeta titulo="Designaciones y grados" icono="nota" sin-relleno="1">
        <div class="v-tabla-wrap"><table class="tabla">
            <thead><tr><th>Designación</th><th>Departamento</th></tr></thead>
            <tbody>
            @foreach($designaciones as $d)
                <tr><td>{{ $d->nombre }}</td><td class="v-mini">{{ $d->departamento->nombre ?? '—' }}</td></tr>
            @endforeach
            </tbody></table></div>
    </x-ui.tarjeta>

    <x-ui.tarjeta titulo="Tipos de empleo y contrato" icono="nota" sin-relleno="1">
        <div class="v-tabla-wrap"><table class="tabla">
            <thead><tr><th>Tipo de empleo</th><th>Tipo de contrato</th><th>Duración</th></tr></thead>
            <tbody>
            @foreach($tiposEmpleo as $t)
                <tr><td>{{ $t->nombre }}</td><td class="v-mini">{{ $tiposContrato[$loop->index]->nombre ?? '—' }}</td><td class="v-mini">{{ $tiposContrato[$loop->index]->duracion_meses ?? '—' }}</td></tr>
            @endforeach
            </tbody></table></div>
    </x-ui.tarjeta>

    <x-ui.tarjeta titulo="Conceptos salariales" icono="dinero" sin-relleno="1">
        <div class="v-tabla-wrap"><table class="tabla">
            <thead><tr><th>Código</th><th>Concepto</th><th>Tipo</th></tr></thead>
            <tbody>
            @forelse($conceptos as $c)
                <tr><td class="v-mono">{{ $c->codigo }}</td><td>{{ $c->nombre }}</td><td class="v-mini">{{ $c->tipo ?? '—' }}</td></tr>
            @empty
                <tr><td colspan="3" class="vacio">Sin conceptos registrados.</td></tr>
            @endforelse
            </tbody></table></div>
    </x-ui.tarjeta>
</div>

<x-ui.tarjeta titulo="Motivos de salida temporal" icono="pausa" class="v-mt">
    <div class="v-pills">
        @foreach($motivosSalida as $m)<span class="v-pill">{{ $m }}</span>@endforeach
    </div>
    <p class="v-nota v-mt">Los catálogos conservan sus históricos: si un motivo deja de usarse se desactiva, no se elimina.</p>
</x-ui.tarjeta>
@endsection
