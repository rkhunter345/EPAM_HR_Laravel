@extends('layouts.app')
@section('titulo', 'Nueva estación IoT')
@section('migas', 'Configuración / IoT')
@section('contenido')
<div class="cab">
    <div><h1>Nueva estación IoT</h1><p>Cámara fija de estación de trabajo o lector de marcación QR</p></div>
</div>

<div class="card">
    <form method="POST" action="{{ route('iot.estacion.guardar') }}">
        @csrf
        <div class="card__cuerpo">
            <div class="form-grid">
                <div class="campo col-4"><label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" placeholder="estacion-1" required></div>
                <div class="campo col-4"><label for="tipo">Tipo *</label>
                    <select id="tipo" name="tipo">
                        <option value="camara" @selected(old('tipo') === 'camara')>Cámara fija</option>
                        <option value="qr" @selected(old('tipo') === 'qr')>Lector QR</option>
                    </select></div>
                <div class="campo col-4"><label for="ubicacion">Ubicación</label>
                    <input type="text" id="ubicacion" name="ubicacion" value="{{ old('ubicacion') }}" placeholder="Piso 2 · Mesa 4"></div>
                <div class="campo col-4"><label for="empleado_id">Empleado asignado</label>
                    <select id="empleado_id" name="empleado_id">
                        <option value="">Sin asignar</option>
                        @foreach($empleados as $id => $nombre)
                            <option value="{{ $id }}" @selected(old('empleado_id') == $id)>{{ $nombre }}</option>
                        @endforeach
                    </select></div>
                <div class="campo col-4 campo-check">
                    <input type="hidden" name="activo" value="0">
                    <input type="checkbox" id="activo" name="activo" value="1" @checked(old('activo', true))>
                    <label for="activo" style="margin:0">Estación activa</label>
                </div>
            </div>
        </div>
        <div class="card__pie">
            <a href="{{ route('iot.index') }}" class="btn">Cancelar</a>
            <button class="btn btn--pri">Guardar estación</button>
        </div>
    </form>
</div>
@endsection
