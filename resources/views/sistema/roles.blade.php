@extends('layouts.app')
@section('titulo', 'Roles y permisos')
@section('seccion', 'Administración')
@section('migas', 'Roles y permisos')

{{-- FASE 2.A: política informativa NO editable. La autorización real la aplica
     el middleware 'rol' en el servidor; los límites máximos por rol no se
     configuran desde la interfaz. --}}

@section('contenido')
<div class="v-cab">
    <div>
        <h1>Roles y permisos</h1>
        <p>Política de permisos por rol y módulo. Es una política fija de mínimo privilegio: los límites máximos de cada rol no se modifican desde la interfaz.</p>
    </div>
</div>

<div class="v-kpis">
    <x-ui.kpi label="Roles" :valor="count($roles)" icono="escudo" tono="violeta" sub="ADMIN, RRHH, SUPERVISOR, EMPLEADO" />
    <x-ui.kpi label="Módulos definidos" :valor="$matriz->count()" icono="config" tono="azul" sub="Alcance fijo por rol" />
    <x-ui.kpi label="Autorización" valor="En el servidor" icono="llave" tono="verde" sub="Middleware rol, no en menús" />
    <x-ui.kpi label="Usuarios" :valor="$usuariosPorRol->sum()" icono="usuario" tono="ambar" sub="Distribuidos por rol" />
</div>

<div class="v-grid v-grid--lado">
    <x-ui.tarjeta titulo="Matriz de permisos (informativa)" icono="escudo" nota="Solo lectura: refleja la política fija aplicada en el servidor" sin-relleno="1">
        <div class="v-tabla-wrap"><table class="tabla">
            <thead><tr><th>Módulo</th>@foreach($roles as $codigo => $nombreRol)<th>{{ $codigo }}</th>@endforeach</tr></thead>
            <tbody>
            @foreach($matriz as $fila)
                <tr>
                    <td><strong>{{ $fila['modulo'] }}</strong></td>
                    @foreach($roles as $codigo => $nombreRol)
                        <td>
                            @if($fila[$codigo])
                                <x-ui.badge valor="Permitido" />
                            @else
                                <span class="v-mini">—</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody></table></div>
        <div style="padding:12px 16px">
            <div class="v-estado v-estado--info"><x-ui.icono name="escudo" :size="16" /><div><strong>Política fija de mínimo privilegio</strong>La matriz no concede ni revoca acceso: esa decisión pertenece al propietario del sistema y se aplica en código. Un ADMIN no puede otorgarse acceso a datos de negocio desde esta pantalla.</div></div>
        </div>
    </x-ui.tarjeta>

    <div>
        <x-ui.tarjeta titulo="Distribución de usuarios" icono="equipo">
            @foreach($roles as $r)
                <div class="v-dato"><span>{{ $r }}</span><strong>{{ $usuariosPorRol[$r] ?? 0 }}</strong></div>
            @endforeach
        </x-ui.tarjeta>
        <x-ui.tarjeta titulo="Alcance máximo por rol" icono="llave">
            @foreach($roles as $codigo => $nombreRol)
                <div class="v-dato"><span>{{ $codigo }}</span><strong style="max-width:260px;font-weight:500;font-size:12.5px;line-height:1.5">{{ $alcance[$codigo] }}</strong></div>
            @endforeach
        </x-ui.tarjeta>
        <x-ui.tarjeta titulo="Controles obligatorios" icono="llave">
            <div class="v-estado v-estado--info"><x-ui.icono name="escudo" :size="16" /><div><strong>Prevención de escalamiento</strong>Nadie puede concederse permisos a sí mismo; los cambios de rol se registran en auditoría.</div></div>
            <div class="v-dato v-mt"><span>Autorización</span><strong>Validada en backend</strong></div>
            <div class="v-dato"><span>Ocultar menús</span><strong>No es suficiente por sí solo</strong></div>
            <div class="v-dato"><span>Auditoría</span><strong>No alterable</strong></div>
        </x-ui.tarjeta>
    </div>
</div>
@endsection
