@extends('layouts.app')
@section('titulo', 'Dashboard técnico')
@section('seccion', 'Administración')
@section('migas', 'Dashboard técnico')

@section('contenido')
<div class="v-cab">
    <div>
        <h1>Dashboard técnico</h1>
        <p>Estado de cuentas, roles, integraciones y auditoría. Este panel no accede a expedientes, nómina ni asistencia.</p>
    </div>
    <div class="v-cab__acc">
        <a class="btn" href="{{ route('sistema.auditoria') }}"><x-ui.icono name="llave" :size="14" /> Auditoría</a>
        <a class="btn btn--pri" href="{{ route('sistema.usuarios') }}"><x-ui.icono name="usuario" :size="14" /> Cuentas</a>
    </div>
</div>

<div class="v-kpis v-kpis--5">
    <x-ui.kpi label="Cuentas activas" :valor="$cuentasActivas" icono="usuario" tono="verde" sub="{{ $cuentasInactivas }} inactivas" />
    <x-ui.kpi label="Roles definidos" :valor="$roles" icono="escudo" tono="violeta" sub="Mínimo privilegio" />
    <x-ui.kpi label="Integraciones operativas" :valor="$operativos" icono="nube" tono="azul" sub="{{ $integraconCaidas }} con incidencia" />
    <x-ui.kpi label="Parámetros sin valor" :valor="$pendientes" icono="config" tono="ambar" sub="Requieren configuración" />
    <x-ui.kpi label="Total de usuarios" :valor="$totalUsuarios" icono="equipo" tono="gris" sub="Incluye cuentas inactivas" />
</div>

<div class="v-grid v-grid--lado">
    <div>
        <x-ui.tarjeta titulo="Integraciones" icono="nube" nota="Credenciales protegidas; el detalle técnico no se expone" sin-relleno="1">
            <div class="v-tabla-wrap"><table class="tabla">
                <thead><tr><th>Servicio</th><th>Tipo</th><th>Estado</th><th>Última sincronización</th><th>Activo</th></tr></thead>
                <tbody>
                @forelse($integraciones as $i)
                    <tr>
                        <td><strong>{{ $i->nombre }}</strong><br><small class="v-mini">{{ \Illuminate\Support\Str::limit($i->detalle, 54) }}</small></td>
                        <td class="v-mini">{{ $i->tipo }}</td>
                        <td><x-ui.badge :valor="$i->estado" /></td>
                        <td class="v-mini">{{ $i->ultima_sincronizacion?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td><x-ui.badge :valor="$i->activo ? 'Activo' : 'Inactivo'" /></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="vacio">Sin integraciones registradas.</td></tr>
                @endforelse
                </tbody></table></div>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Parámetros del sistema" icono="config" nota="Todo cambio queda auditado con valor anterior y nuevo" sin-relleno="1">
            <div class="v-tabla-wrap"><table class="tabla">
                <thead><tr><th>Grupo</th><th>Parámetro</th><th>Valor</th><th>Unidad</th></tr></thead>
                <tbody>
                @forelse($parametros as $p)
                    <tr>
                        <td class="v-mini">{{ $p->grupo }}</td>
                        <td><strong>{{ $p->etiqueta }}</strong><br><small class="v-mini">{{ $p->clave }}</small></td>
                        <td>{{ $p->valor !== null && $p->valor !== '' ? $p->valor : '—' }}</td>
                        <td class="v-mini">{{ $p->unidad ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="vacio">Sin parámetros registrados.</td></tr>
                @endforelse
                </tbody></table></div>
        </x-ui.tarjeta>
    </div>

    <div>
        <x-ui.tarjeta titulo="Eventos recientes de auditoría" icono="llave" nota="Registro de solo lectura" sin-relleno="1">
            <div class="v-lista">
                @forelse($eventos as $e)
                    <div class="v-lista__item">
                        <span class="v-lista__ico tono-gris"><x-ui.icono name="llave" :size="14" /></span>
                        <div class="v-lista__txt"><strong>{{ $e->accion ?? 'Acción' }}</strong><small>{{ \Illuminate\Support\Str::limit($e->detalle ?? '', 58) }}</small></div>
                        <span class="v-lista__hora">{{ $e->created_at?->format('d/m H:i') }}</span>
                    </div>
                @empty
                    <div class="v-lista__item"><div class="v-lista__txt"><strong>Sin eventos</strong><small>Aún no hay acciones registradas.</small></div></div>
                @endforelse
            </div>
        </x-ui.tarjeta>

        <x-ui.tarjeta titulo="Alcance del rol administrador" icono="escudo">
            <div class="v-estado v-estado--info"><x-ui.icono name="escudo" :size="16" /><div><strong>Sin acceso a datos laborales</strong>El administrador configura la plataforma pero no consulta expedientes, asistencia ni remuneraciones sin un rol de negocio explícito.</div></div>
            <div class="v-dato v-mt"><span>Separación de roles</span><strong>Aplicada</strong></div>
            <div class="v-dato"><span>Auditoría</span><strong>Solo lectura</strong></div>
            <div class="v-dato"><span>Decisiones laborales automáticas</span><strong>Ninguna</strong></div>
        </x-ui.tarjeta>
    </div>
</div>
@endsection
