@extends('layouts.app')
@section('titulo', 'Empleados')
@section('migas', 'Empleados')
@section('contenido')
<div class="cab">
    <div>
        <h1>Empleados</h1>
        <p>Directorio de colaboradores de MULTISERVICIOS EPAM E.I.R.L.</p>
    </div>
    <div class="cab__acciones">
        <a href="{{ route('empleados.index') }}" class="btn"><x-ui.icono name="ver" :size="14" /> Vista de lista</a>
        <a href="{{ route('reportes.index') }}" class="btn">Importar empleados</a>
        <a href="{{ route('empleados.create') }}" class="btn btn--pri"><x-ui.icono name="mas" :size="15" /> Agregar empleado</a>
    </div>
</div>

<div class="grid grid--4" style="margin-bottom:14px">
    <div class="kpi">
        <div class="kpi__cabeza"><span class="kpi__icono tono-azul"><x-ui.icono name="empleados" :size="13" /></span><span class="kpi__label">Total de empleados</span></div>
        <div class="kpi__valor">{{ $total }}</div>
        <div class="kpi__sub">Registrados en el padrón</div>
    </div>
    <div class="kpi">
        <div class="kpi__cabeza"><span class="kpi__icono tono-azul"><x-ui.icono name="mas" :size="13" /></span><span class="kpi__label">Nuevos este año</span></div>
        <div class="kpi__valor">{{ $nuevos }}</div>
        <div class="kpi__sub">Ingresaron en {{ now()->year }}</div>
    </div>
    <div class="kpi">
        <div class="kpi__cabeza"><span class="kpi__icono tono-ambar"><x-ui.icono name="reloj" :size="13" /></span><span class="kpi__label">En proceso</span></div>
        <div class="kpi__valor">{{ $proceso }}</div>
        <div class="kpi__sub">En proceso de contratación</div>
    </div>
    <div class="kpi">
        <div class="kpi__cabeza"><span class="kpi__icono tono-rojo"><x-ui.icono name="ausencia" :size="13" /></span><span class="kpi__label">Salidas este año</span></div>
        <div class="kpi__valor">{{ $salidas }}</div>
        <div class="kpi__sub">Cesados o inactivos</div>
    </div>
</div>

<div class="card">
    <form method="GET" action="{{ route('empleados.index') }}" class="filtros">
        <div class="campo filtros__texto">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre, identificador o departamento...">
        </div>
        <div class="campo" style="min-width:170px">
            <input type="text" name="codigo" value="{{ request('codigo') }}" placeholder="Identificador">
        </div>
        <div class="campo">
            <select name="departamento_id">
                <option value="">Departamento</option>
                @foreach($departamentos as $id => $nombre)
                    <option value="{{ $id }}" @selected(request('departamento_id') == $id)>{{ $nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="campo">
            <select name="designacion_id">
                <option value="">Designación</option>
                @foreach($designaciones as $id => $nombre)
                    <option value="{{ $id }}" @selected(request('designacion_id') == $id)>{{ $nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="campo" style="min-width:130px">
            <select name="estado">
                <option value="">Estado</option>
                @foreach(\App\Models\Empleados\Empleado::ESTADOS as $estado)
                    <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $estado }}</option>
                @endforeach
            </select>
        </div>
        <div class="campo" style="min-width:150px">
            <select name="orden">
                <option value="nombre" @selected(request('orden') === 'nombre')>Ordenar por: nombre</option>
                <option value="reciente" @selected(request('orden') === 'reciente')>Más recientes</option>
                <option value="codigo" @selected(request('orden') === 'codigo')>Identificador</option>
                <option value="salario" @selected(request('orden') === 'salario')>Salario</option>
            </select>
        </div>
        <button class="btn"><x-ui.icono name="buscar" :size="14" /> Filtrar</button>
        <a href="{{ route('empleados.index') }}" class="btn btn--ico" title="Limpiar filtros"><x-ui.icono name="salir" :size="14" /></a>
    </form>

    <div class="tabla-wrap">
        <table class="tabla">
            <thead>
                <tr>
                    <th style="width:28px"><input type="checkbox" aria-label="Seleccionar todo"></th>
                    <th>Nombre completo</th>
                    <th>Identificador</th>
                    <th>Departamento</th>
                    <th>Designación</th>
                    <th>Sucursal</th>
                    <th>Estado</th>
                    <th style="text-align:right">Acciones</th>
                </tr>
            </thead>
            <tbody>
            @forelse($empleados as $empleado)
                <tr>
                    <td><input type="checkbox" aria-label="Seleccionar {{ $empleado->nombre_completo }}"></td>
                    <td>
                        <div class="celda-persona">
                            <span class="avatar avatar--foto">{{ $empleado->iniciales() }}</span>
                            <div class="celda-persona__datos">
                                <a href="{{ route('empleados.show', $empleado) }}"><strong>{{ $empleado->nombre_completo }}</strong></a>
                                <small>{{ $empleado->correo_personal ?? 'sin correo registrado' }}</small>
                            </div>
                        </div>
                    </td>
                    <td>{{ $empleado->codigo }}</td>
                    <td>{{ $empleado->departamento->nombre ?? '—' }}</td>
                    <td>{{ $empleado->designacion->nombre ?? '—' }}</td>
                    <td>{{ $empleado->sucursal->nombre ?? '—' }}</td>
                    <td><x-ui.badge :valor="$empleado->estado" /></td>
                    <td>
                        <div class="acciones">
                            <a class="btn btn--ico" href="{{ route('empleados.show', $empleado) }}" title="Ver ficha"><x-ui.icono name="ver" :size="15" /></a>
                            <a class="btn btn--ico" href="{{ route('empleados.edit', $empleado) }}" title="Editar"><x-ui.icono name="lapiz" :size="15" /></a>
                            <form method="POST" action="{{ route('empleados.destroy', $empleado) }}" onsubmit="return confirm('¿Eliminar a {{ $empleado->nombre_completo }} del padrón?');">
                                @csrf @method('DELETE')
                                <button class="btn btn--ico" title="Eliminar"><x-ui.icono name="basura" :size="15" /></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="vacio">No se encontraron empleados con esos filtros.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card__pie">
        <small style="color:var(--texto-3)">{{ $empleados->total() }} empleados · página {{ $empleados->currentPage() }} de {{ $empleados->lastPage() }}</small>
        {{ $empleados->links('components.crud.paginacion') }}
    </div>
</div>
@endsection
