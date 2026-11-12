<div class="cab">
    <div>
        <h1>{{ $titulo }}</h1>
        <p>{{ $descripcion }}</p>
    </div>
    <div class="cab__acciones">
        <a href="{{ route($ruta.'.create') }}" class="btn btn--pri"><x-ui.icono name="mas" :size="15" /> Nuevo {{ $singular }}</a>
    </div>
</div>

<div class="card">
    <form method="GET" action="{{ route($ruta.'.index') }}" class="filtros">
        <div class="campo filtros__texto">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar {{ $singular }} por nombre o código">
        </div>
        <button class="btn"><x-ui.icono name="buscar" :size="14" /> Buscar</button>
        @if(request('q'))
            <a href="{{ route($ruta.'.index') }}" class="btn btn--sm">Limpiar</a>
        @endif
    </form>

    <div class="tabla-wrap">
        <table class="tabla">
            <thead>
                <tr>
                    @foreach($columnas as $col)
                        <th>{{ $col['label'] }}</th>
                    @endforeach
                    <th style="text-align:right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registros as $registro)
                    <tr>
                        @foreach($columnas as $col)
                            @php $valor = data_get($registro, $col['campo']); @endphp
                            <td>
                                @if(!empty($col['badge']))
                                    <x-ui.badge :valor="$valor" />
                                @elseif($loop->first)
                                    <a href="{{ route($ruta.'.show', $registro) }}"><strong>{{ $valor !== null && $valor !== '' ? $valor : '—' }}</strong></a>
                                @else
                                    {{ $valor !== null && $valor !== '' ? $valor : '—' }}
                                @endif
                            </td>
                        @endforeach
                        <td>
                            <div class="acciones">
                                <a class="btn btn--ico" href="{{ route($ruta.'.show', $registro) }}" title="Ver detalle"><x-ui.icono name="ver" :size="15" /></a>
                                <a class="btn btn--ico" href="{{ route($ruta.'.edit', $registro) }}" title="Editar"><x-ui.icono name="lapiz" :size="15" /></a>
                                <form method="POST" action="{{ route($ruta.'.destroy', $registro) }}" onsubmit="return confirm('¿Eliminar este registro? Esta acción no se puede deshacer.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn--ico" title="Eliminar"><x-ui.icono name="basura" :size="15" /></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ count($columnas) + 1 }}" class="vacio">Todavía no hay {{ $titulo }} registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card__pie">
        <small style="color:var(--texto-3)">Mostrando {{ $registros->firstItem() ?? 0 }}–{{ $registros->lastItem() ?? 0 }} de {{ $registros->total() }} {{ $singular }}</small>
        {{ $registros->links('components.crud.paginacion') }}
    </div>
</div>
