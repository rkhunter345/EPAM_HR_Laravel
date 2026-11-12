@php $editando = $registro->exists; @endphp

<div class="cab">
    <div>
        <h1>{{ $editando ? 'Editar' : 'Nuevo' }} {{ $singular }}</h1>
        <p>{{ $descripcion }}</p>
    </div>
    <div class="cab__acciones">
        <a href="{{ route($ruta.'.index') }}" class="btn">Ver listado</a>
    </div>
</div>

<form method="POST" action="{{ $editando ? route($ruta.'.update', $registro) : route($ruta.'.store') }}">
    @csrf
    @if($editando)
        @method('PUT')
    @endif

    <div class="card">
        <div class="seccion">
            <h2 class="seccion-titulo"><x-ui.icono name="solicitud" :size="15" /> Datos de {{ $singular }}</h2>
            <p class="seccion-nota">Los campos marcados con * son obligatorios.</p>

            <div class="form-grid">
                @foreach($campos as $campo)
                    @php
                        $nombre = $campo['name'];
                        $valor = old($nombre, data_get($registro, $nombre));
                        $ancho = $campo['col'] ?? 6;
                    @endphp

                    @if(($campo['type'] ?? 'text') === 'checkbox')
                        <div class="campo campo--check col-{{ $ancho }}">
                            <input type="hidden" name="{{ $nombre }}" value="0">
                            <input type="checkbox" id="f_{{ $nombre }}" name="{{ $nombre }}" value="1" @checked($valor)>
                            <label for="f_{{ $nombre }}">{{ $campo['label'] }}</label>
                        </div>
                    @else
                        <div class="campo col-{{ $ancho }}">
                            <label for="f_{{ $nombre }}">{{ $campo['label'] }}@if(!empty($campo['req'])) *@endif</label>

                            @if(in_array($campo['type'] ?? 'text', ['select', 'select_dinamico'], true))
                                @php
                                    $lista = ($campo['type'] === 'select_dinamico')
                                        ? ($opciones[$campo['fuente']] ?? collect())
                                        : collect($campo['opciones'] ?? []);
                                @endphp
                                <select id="f_{{ $nombre }}" name="{{ $nombre }}">
                                    <option value="">Seleccionar</option>
                                    @foreach($lista as $clave => $texto)
                                        <option value="{{ $clave }}" @selected((string) $valor === (string) $clave)>{{ $texto }}</option>
                                    @endforeach
                                </select>
                            @elseif(($campo['type'] ?? 'text') === 'textarea')
                                <textarea id="f_{{ $nombre }}" name="{{ $nombre }}" rows="3">{{ $valor }}</textarea>
                            @elseif(($campo['type'] ?? 'text') === 'password')
                                <input type="password" id="f_{{ $nombre }}" name="{{ $nombre }}" value="" autocomplete="new-password"
                                       placeholder="{{ $editando ? 'Dejar vacío para no cambiarla' : '' }}">
                            @else
                                <input type="{{ $campo['type'] ?? 'text' }}" id="f_{{ $nombre }}" name="{{ $nombre }}"
                                       value="{{ ($campo['type'] ?? '') === 'time' ? substr((string) $valor, 0, 5) : $valor }}"
                                       @if(($campo['type'] ?? '') === 'number') step="any" @endif>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="form-acciones">
            <a href="{{ route($ruta.'.index') }}" class="btn">Cancelar</a>
            <button class="btn btn--pri">{{ $editando ? 'Guardar cambios' : 'Crear ' }}{{ $editando ? '' : $singular }}</button>
        </div>
    </div>
</form>
