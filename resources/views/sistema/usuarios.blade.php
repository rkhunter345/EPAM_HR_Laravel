@extends('layouts.app')
@section('titulo', 'Usuarios y cuentas')
@section('seccion', 'Administración')
@section('migas', 'Usuarios y cuentas')

@section('contenido')
<div class="v-cab">
    <div>
        <h1>Usuarios y cuentas</h1>
        <p>Crea y administra cuentas de acceso. La vinculación con el expediente del trabajador habilita los módulos personales.</p>
    </div>
</div>

<div class="v-kpis">
    <x-ui.kpi label="Cuentas activas" :valor="$activas" icono="usuario" tono="verde" sub="Con acceso al sistema" />
    <x-ui.kpi label="Cuentas inactivas" :valor="$inactivas" icono="ausencia" tono="rojo" sub="Sin acceso" />
    <x-ui.kpi label="Supervisores" :valor="$supervisores" icono="equipo" tono="violeta" sub="Con equipo asignado" />
    <x-ui.kpi label="Usuarios totales" :valor="$usuarios->count()" icono="empleados" tono="azul" sub="Incluye todos los roles" />
</div>

<div class="v-grid v-grid--lado">
    <x-ui.tarjeta titulo="Nueva cuenta" icono="usuario" nota="Contraseña temporal: epam2026 (debe cambiarse al primer ingreso)">
        <form method="POST" action="{{ route('sistema.usuarios.guardar') }}">
            @csrf
            <div class="v-form-2">
                <div class="campo"><label>Nombre completo *</label><input type="text" name="name" value="{{ old('name') }}" required></div>
                <div class="campo"><label>Usuario *</label><input type="text" name="usuario" value="{{ old('usuario') }}" required></div>
                <div class="campo"><label>Correo</label><input type="email" name="email" value="{{ old('email') }}"></div>
                <div class="campo"><label>Rol *</label>
                    <select name="rol" required>
                        @foreach(\App\Models\User::ROLES as $codigo => $nombreRol)<option value="{{ $codigo }}">{{ $nombreRol }}</option>@endforeach
                    </select>
                </div>
            </div>
            <div class="campo"><label>Vincular a expediente</label>
                <select name="empleado_id"><option value="">Sin vinculación</option>
                    @foreach($empleados as $e)<option value="{{ $e->id }}">{{ $e->codigo }} · {{ $e->nombre_completo }}</option>@endforeach
                </select>
            </div>
            <button class="btn btn--pri"><x-ui.icono name="mas" :size="14" /> Crear cuenta</button>
        </form>
    </x-ui.tarjeta>

    <x-ui.tarjeta titulo="Reglas de seguridad" icono="escudo">
        <div class="v-estado v-estado--info"><x-ui.icono name="escudo" :size="16" /><div><strong>Mínimo privilegio</strong>Cada cuenta recibe solo el rol necesario; los permisos sensibles requieren aprobación y quedan auditados.</div></div>
        <div class="v-dato v-mt"><span>Contraseña temporal</span><strong>epam2026</strong></div>
        <div class="v-dato"><span>Cambio obligatorio</span><strong>Primer ingreso</strong></div>
        <div class="v-dato"><span>Autoprotección</span><strong>No puede desactivar su cuenta</strong></div>
    </x-ui.tarjeta>
</div>

<x-ui.tarjeta titulo="Cuentas registradas" icono="equipo" sin-relleno="1" class="v-mt">
    <div class="v-tabla-wrap"><table class="tabla">
        <thead><tr><th>Usuario</th><th>Nombre</th><th>Rol</th><th>Expediente</th><th>Estado</th><th>Acción</th></tr></thead>
        <tbody>
        @foreach($usuarios as $u)
            <tr>
                <td><div class="v-persona"><span class="v-avatar">{{ $u->inicial }}</span><div><strong>{{ $u->usuario }}</strong><small>{{ $u->email ?: 'sin correo' }}</small></div></div></td>
                <td>{{ $u->name }}</td>
                <td><x-ui.badge :valor="$u->rol" /></td>
                <td class="v-mini">{{ $u->empleado_id ? 'Vinculado (#'.$u->empleado_id.')' : 'Sin vínculo' }}</td>
                <td><x-ui.badge :valor="$u->activo ? 'Activo' : 'Inactivo'" /></td>
                <td>
                    <form method="POST" action="{{ route('sistema.usuarios.estado', $u) }}">@csrf
                        <button class="btn btn--sm">{{ $u->activo ? 'Desactivar' : 'Activar' }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody></table></div>
</x-ui.tarjeta>
@endsection
