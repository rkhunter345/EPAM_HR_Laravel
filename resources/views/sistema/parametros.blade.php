@extends('layouts.app')
@section('titulo', 'Parámetros del sistema')
@section('seccion', 'Administración')
@section('migas', 'Parámetros del sistema')

@section('contenido')
<div class="v-cab">
    <div>
        <h1>Parámetros del sistema</h1>
        <p>Configuración técnica y de asistencia. Cada cambio conserva el valor anterior en la auditoría.</p>
    </div>
</div>

<form method="POST" action="{{ route('sistema.parametros.guardar') }}">
    @csrf
    <div class="v-grid v-grid--2">
        @foreach($grupos as $g)
            <x-ui.tarjeta :titulo="'Grupo: '.$g" icono="config" :nota="'Parámetros de '.$g">
                @foreach($parametros->where('grupo', $g) as $p)
                    <div class="campo">
                        <label>{{ $p->etiqueta }} <span class="v-mini">{{ $p->clave }}</span></label>
                        @if($p->tipo === 'booleano')
                            <select name="{{ $p->clave }}">
                                <option value="1" @selected((string) $p->valor === '1')>Sí</option>
                                <option value="0" @selected((string) $p->valor === '0')>No</option>
                            </select>
                        @elseif($p->tipo === 'tiempo' || $p->tipo === 'numero')
                            <input type="number" name="{{ $p->clave }}" value="{{ $p->valor }}" step="1" min="0">
                        @else
                            <input type="text" name="{{ $p->clave }}" value="{{ $p->valor }}">
                        @endif
                        @if($p->descripcion)<small class="v-mini">{{ $p->descripcion }}</small>@endif
                    </div>
                @endforeach
            </x-ui.tarjeta>
        @endforeach
    </div>
    <div class="v-mt">
        <button class="btn btn--pri"><x-ui.icono name="check" :size="14" /> Guardar parámetros</button>
    </div>
</form>

<div class="v-grid v-grid--2 v-mt">
    <x-ui.tarjeta titulo="Parámetros de seguridad" icono="escudo">
        @forelse($seguridad as $p)
            <div class="v-dato"><span>{{ $p->etiqueta }}</span><strong>{{ $p->valor ?: '—' }}</strong></div>
        @empty
            <div class="v-estado v-estado--neutro"><x-ui.icono name="config" :size="16" /><div>Sin parámetros de seguridad.</div></div>
        @endforelse
    </x-ui.tarjeta>
    <x-ui.tarjeta titulo="Notificaciones" icono="campana">
        @forelse($notificaciones as $p)
            <div class="v-dato"><span>{{ $p->etiqueta }}</span><strong>{{ $p->valor ?: '—' }}</strong></div>
        @empty
            <div class="v-estado v-estado--neutro"><x-ui.icono name="campana" :size="16" /><div>Sin parámetros de notificación.</div></div>
        @endforelse
    </x-ui.tarjeta>
</div>
@endsection
