<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Panel') · Multiservicios EPAM E.I.R.L.</title>
    <link rel="stylesheet" href="{{ asset('css/epam.css') }}">
    <link rel="stylesheet" href="{{ asset('css/v10.css') }}">
    <link rel="stylesheet" href="{{ asset('css/v11.css') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('img/favicon.svg') }}">
    <link rel="alternate icon" type="image/png" href="{{ asset('img/favicon.png') }}">
</head>
<body>
@php
    $u = auth()->user();
    $rol = $u?->rol ?? 'EMPLEADO';
    $ruta = request()->route()?->getName();

    $menu = [];

    // ------------------------- Mi espacio (empleado y supervisor) ------------
    if (in_array($rol, ['EMPLEADO', 'SUPERVISOR'], true)) {
    $menu[] = ['grupo' => 'Mi espacio', 'items' => [
        ['r' => 'espacio.inicio', 'l' => 'Inicio', 'i' => 'tablero'],
        ['r' => 'espacio.asistencia', 'l' => 'Asistencia y jornada', 'i' => 'reloj'],
        ['r' => 'espacio.monitoreo', 'l' => 'Monitoreo de presencia', 'i' => 'camara'],
        ['r' => 'espacio.historial', 'l' => 'Historial', 'i' => 'calendario'],
        ['r' => 'espacio.alertas', 'l' => 'Alertas', 'i' => 'campana'],
        ['r' => 'espacio.justificaciones', 'l' => 'Justificaciones', 'i' => 'solicitud'],
        ['r' => 'espacio.vacaciones', 'l' => 'Vacaciones y permisos', 'i' => 'ausencia'],
        ['r' => 'espacio.boletas', 'l' => 'Mis boletas de pago', 'i' => 'planilla'],
    ]];
    }

    if ($rol === 'SUPERVISOR') {
        $menu[] = ['grupo' => 'Supervisión', 'items' => [
            ['r' => 'equipo.panel', 'l' => 'Panel de supervisión', 'i' => 'equipo'],
            ['r' => 'equipo.asistencia', 'l' => 'Asistencia del equipo', 'i' => 'reloj'],
            ['r' => 'equipo.monitoreo', 'l' => 'Monitoreo del equipo', 'i' => 'camara'],
            ['r' => 'equipo.solicitudes', 'l' => 'Solicitudes del equipo', 'i' => 'solicitud'],
            ['r' => 'equipo.reportes', 'l' => 'Reportes del equipo', 'i' => 'reporte'],
        ]];
    }

    if ($rol === 'RRHH') {
        $menu[] = ['grupo' => 'Recursos Humanos', 'items' => [
            ['r' => 'rrhh.panel', 'l' => 'Dashboard de RR. HH.', 'i' => 'tablero'],
            ['r' => 'rrhh.personal', 'l' => 'Personal', 'i' => 'empleados'],
            ['r' => 'rrhh.asistencia', 'l' => 'Asistencia general', 'i' => 'reloj'],
            ['r' => 'rrhh.iot', 'l' => 'IoT y alertas', 'i' => 'camara'],
            ['r' => 'rrhh.justificaciones', 'l' => 'Justificaciones', 'i' => 'solicitud'],
            ['r' => 'rrhh.emergencias', 'l' => 'Emergencias médicas', 'i' => 'medico'],
            ['r' => 'rrhh.vacaciones', 'l' => 'Vacaciones y permisos', 'i' => 'ausencia'],
            ['r' => 'rrhh.nomina', 'l' => 'Nómina', 'i' => 'dinero'],
            ['r' => 'rrhh.boletas', 'l' => 'Boletas de pago', 'i' => 'planilla'],
            ['r' => 'rrhh.reportes', 'l' => 'Reportes de RR. HH.', 'i' => 'reporte'],
        ]];
    }

    if ($rol === 'ADMIN') {
        $menu[] = ['grupo' => 'Administración', 'items' => [
            ['r' => 'sistema.panel', 'l' => 'Dashboard técnico', 'i' => 'tablero'],
            ['r' => 'sistema.usuarios', 'l' => 'Usuarios y cuentas', 'i' => 'usuario'],
            ['r' => 'sistema.roles', 'l' => 'Roles y permisos', 'i' => 'escudo'],
            ['r' => 'sistema.parametros', 'l' => 'Parámetros del sistema', 'i' => 'config'],
            ['r' => 'sistema.tolerancias', 'l' => 'Asistencia y tolerancias', 'i' => 'reloj'],
            ['r' => 'sistema.catalogos', 'l' => 'Catálogos', 'i' => 'nota'],
            ['r' => 'sistema.integraciones', 'l' => 'Integraciones', 'i' => 'nube'],
            ['r' => 'sistema.auditoria', 'l' => 'Auditoría', 'i' => 'llave'],
        ]];
    }

    $menu[] = ['grupo' => 'General', 'items' => [
        ['r' => 'asistente.index', 'l' => 'Asistente virtual', 'i' => 'chat'],
        ['r' => 'perfil', 'l' => 'Mi perfil', 'i' => 'usuario'],
    ]];
@endphp

<div class="shell">
    <aside class="side" id="barra">
        <div class="side__brand">
            <x-ui.marca variante="claro" :compacta="true" />
            <div>
                <span class="marca__txt" style="font-size:9.6px;color:#fff;letter-spacing:.07em">
                    <strong style="font-weight:800">MULTISERVICIOS</strong>
                    <span style="font-size:8.6px">EPAM E.I.R.L.</span>
                </span>
                <small>Sistema de asistencia y nómina</small>
            </div>
        </div>
        <nav class="side__nav">
            @foreach($menu as $g)
                @php
                    $hayActivo = collect($g['items'])->contains(fn ($i) => $ruta && str_starts_with($ruta, explode('.', $i['r'])[0].'.'));
                @endphp
                <button type="button" class="side__grupo" data-grupo>
                    <span>{{ $g['grupo'] }}</span><span>▾</span>
                </button>
                <div class="side__items">
                    @foreach($g['items'] as $item)
                        <a href="{{ route($item['r']) }}" class="side__link {{ $ruta === $item['r'] ? 'is-active' : '' }}">
                            <x-ui.icono :name="$item['i']" :size="15" /><span>{{ $item['l'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>
        <div class="side__pie">
            <span class="v-avatar v-avatar--navy">{{ $u?->inicial }}</span>
            <div>
                <strong>{{ $u?->name }}</strong>
                <small>{{ $u?->nombre_rol }}</small>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="side__salir" title="Cerrar sesión"><x-ui.icono name="salir" :size="15" /></button>
            </form>
        </div>
    </aside>

    <div class="main">
        <header class="top">
            <div class="top__migas">
                <button type="button" class="btn btn--fantasma btn--sm" id="btn-menu" style="padding:0 6px">
                    <x-ui.icono name="organigrama" :size="15" />
                </button>
                <x-ui.marca variante="oscuro" :compacta="true" class="top__marca" />
                <a href="{{ route('tablero') }}">Inicio</a>
                <span>/</span>
                <span>@yield('seccion', 'Mi espacio')</span>
                <span>/</span>
                <strong>@yield('migas', 'Panel')</strong>
            </div>
            <div class="top__der">
                @if(in_array($rol, ['EMPLEADO', 'SUPERVISOR'], true))
                <a class="top__bell" href="{{ route('espacio.alertas') }}" title="Alertas">
                    <x-ui.icono name="campana" :size="17" />
                    @if(($alertasTop ?? 0) > 0)<span class="top__punto">{{ $alertasTop }}</span>@endif
                </a>
                @endif
                <span class="top__rol"><x-ui.icono name="escudo" :size="12" /> {{ $u?->nombre_rol }}</span>
                <div class="top__usuario">
                    <span class="v-avatar">{{ $u?->inicial }}</span>
                    <div style="line-height:1.25">
                        <strong style="display:block;font-size:11.5px">{{ $u?->name }}</strong>
                        <small style="font-size:10px;color:var(--v-texto-3)">{{ now()->locale('es')->translatedFormat('D, d M Y') }}</small>
                    </div>
                </div>
            </div>
        </header>

        <main class="contenido">
            @if(session('ok'))<div class="aviso aviso--ok v-mb">{{ session('ok') }}</div>@endif
            @if(session('error'))<div class="aviso aviso--error v-mb">{{ session('error') }}</div>@endif
            @if($errors->any())
                <div class="aviso aviso--error v-mb">
                    <strong>Revise el formulario:</strong>
                    <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('contenido')
        </main>
    </div>
</div>

<script>
(function () {
    var g = {};
    try { g = JSON.parse(localStorage.getItem('epam-v10') || '{}'); } catch (e) {}
    document.querySelectorAll('[data-grupo]').forEach(function (b) {
        var items = b.nextElementSibling;
        var clave = (b.textContent || '').trim();
        var abierto = Object.prototype.hasOwnProperty.call(g, clave) ? g[clave] : true;
        items.style.display = abierto ? 'flex' : 'none';
        b.addEventListener('click', function () {
            var nuevo = items.style.display === 'none';
            items.style.display = nuevo ? 'flex' : 'none';
            g[clave] = nuevo;
            try { localStorage.setItem('epam-v10', JSON.stringify(g)); } catch (e) {}
        });
    });
    var btn = document.getElementById('btn-menu');
    if (btn) { btn.addEventListener('click', function () { document.getElementById('barra').classList.toggle('is-abierto'); }); }
})();
</script>
@stack('scripts')
</body>
</html>
