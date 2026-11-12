@extends('layouts.app')
@section('titulo', 'Mi perfil')
@section('seccion', 'General')
@section('migas', 'Mi perfil')

@section('contenido')
<div class="v-cab">
    <div>
        <h1>Mi perfil</h1>
        <p>Actualiza tus datos de contacto y tu contraseña. Los cambios quedan registrados en la auditoría.</p>
    </div>
</div>

<div class="v-perfil-cab">
    <span class="v-avatar v-avatar--lg">{{ $usuario->inicial ?? 'U' }}</span>
    <div>
        <h2 style="margin:0;font-size:17px">{{ $usuario->name }}</h2>
        <p class="v-mini" style="margin:2px 0 0">{{ $usuario->usuario }} · {{ $usuario->nombre_rol }}</p>
    </div>
    <div class="v-cab__acc">
        <span class="v-pill">{{ $usuario->activo ? 'Cuenta activa' : 'Cuenta inactiva' }}</span>
        <span class="v-pill">{{ $sesiones }} sesión(es)</span>
    </div>
</div>

<div class="v-grid v-grid--lado">
    <x-ui.tarjeta titulo="Datos de la cuenta" icono="usuario" nota="El usuario de acceso no puede modificarse desde aquí">
        <form method="POST" action="{{ route('perfil.actualizar') }}">
            @csrf @method('PUT')
            <div class="v-form-2">
                <div class="campo"><label>Nombre completo *</label><input type="text" name="name" value="{{ old('name', $usuario->name) }}" required></div>
                <div class="campo"><label>Correo electrónico</label><input type="email" name="email" value="{{ old('email', $usuario->email) }}"></div>
            </div>
            <div class="campo"><label>Usuario</label><input type="text" value="{{ $usuario->usuario }}" disabled></div>
            <div class="campo"><label>Rol</label><input type="text" value="{{ $usuario->nombre_rol }}" disabled></div>
            <button class="btn btn--pri"><x-ui.icono name="check" :size="14" /> Guardar cambios</button>
        </form>
    </x-ui.tarjeta>

    <div>
        <x-ui.tarjeta titulo="Cambio de contraseña" icono="llave" nota="Mínimo 8 caracteres">
            <form method="POST" action="{{ route('perfil.contrasena') }}">
                @csrf @method('PUT')
                <div class="campo"><label>Contraseña actual *</label><input type="password" name="actual" required></div>
                <div class="campo"><label>Nueva contraseña *</label><input type="password" name="password" required></div>
                <div class="campo"><label>Confirmar nueva contraseña *</label><input type="password" name="password_confirmation" required></div>
                <button class="btn btn--pri"><x-ui.icono name="candado" :size="14" /> Actualizar contraseña</button>
            </form>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Mi expediente" icono="empleados">
            @if($empleado)
                <div class="v-dato"><span>Código</span><strong>{{ $empleado->codigo }}</strong></div>
                <div class="v-dato"><span>Área</span><strong>{{ $empleado->departamento->nombre ?? '—' }}</strong></div>
                <div class="v-dato"><span>Cargo</span><strong>{{ $empleado->designacion->nombre ?? '—' }}</strong></div>
                <div class="v-dato"><span>Estado</span><strong>{{ $empleado->estado }}</strong></div>
            @else
                <div class="v-estado v-estado--warn"><x-ui.icono name="alerta" :size="16" /><div><strong>Sin expediente vinculado</strong>Solicite al Administrador la vinculación de su cuenta con su expediente de trabajador.</div></div>
            @endif
        </x-ui.tarjeta>
    </div>
</div>
@endsection
