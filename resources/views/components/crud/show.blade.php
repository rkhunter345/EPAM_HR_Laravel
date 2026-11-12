@php
    $etiqueta = data_get($registro, $etiquetaCampo ?? 'nombre') ?: ('#'.$registro->getKey());
@endphp

<div class="cab">
    <div>
        <h1>{{ $titulo }}</h1>
        <p>{{ $etiqueta }}</p>
    </div>
    <div class="cab__acciones">
        <a href="{{ route($ruta.'.index') }}" class="btn">Volver</a>
        <a href="{{ route($ruta.'.edit', $registro) }}" class="btn btn--pri"><x-ui.icono name="lapiz" :size="14" /> Editar</a>
    </div>
</div>

<div class="card">
    <div class="card__cab"><h2>{{ $singular }} · {{ $etiqueta }}</h2></div>
    <div class="card__cuerpo">
        <dl class="detalle">
            @foreach($columnas as $col)
                @php $valor = data_get($registro, $col['campo']); @endphp
                <div class="detalle__item">
                    <dt>{{ $col['label'] }}</dt>
                    <dd>
                        @if(!empty($col['badge']))
                            <x-ui.badge :valor="$valor" />
                        @else
                            {{ $valor !== null && $valor !== '' ? $valor : '—' }}
                        @endif
                    </dd>
                </div>
            @endforeach
            <div class="detalle__item">
                <dt>Creado</dt>
                <dd>{{ optional($registro->created_at)->format('d/m/Y H:i') ?? '—' }}</dd>
            </div>
            <div class="detalle__item">
                <dt>Última actualización</dt>
                <dd>{{ optional($registro->updated_at)->format('d/m/Y H:i') ?? '—' }}</dd>
            </div>
        </dl>
    </div>
    <div class="form-acciones">
        <form method="POST" action="{{ route($ruta.'.destroy', $registro) }}" onsubmit="return confirm('¿Eliminar este registro?');">
            @csrf
            @method('DELETE')
            <button class="btn btn--peligro"><x-ui.icono name="basura" :size="14" /> Eliminar</button>
        </form>
    </div>
</div>
