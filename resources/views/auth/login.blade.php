<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar · Multiservicios EPAM E.I.R.L.</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('img/favicon.svg') }}">
    <link rel="alternate icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/epam.css') }}">
    <link rel="stylesheet" href="{{ asset('css/v10.css') }}">
    <link rel="stylesheet" href="{{ asset('css/v11.css') }}">
</head>
<body class="acceso-body">
<div class="acceso">

    {{-- --------------------------- lado narrativo --------------------------- --}}
    <section class="acceso__arte">
        <div class="acceso__rejilla"></div>

        <div class="acceso__top">
            <x-ui.marca variante="claro" tam="lg" />
            <span class="acceso__sello">Huánuco · Perú</span>
        </div>

        <div class="acceso__mid">
            <p class="acceso__eyebrow">Plataforma corporativa</p>
            <h1>Sistema inteligente de<br><em>asistencia y nómina</em></h1>
            <p class="acceso__bajada">
                Marcación de jornada, monitoreo de presencia con IoT, gestión de casos y planilla
                con reportes auditables. Cada acción queda registrada con responsable y fecha.
            </p>

            <ul class="acceso__lista">
                <li><x-ui.icono name="reloj" :size="15" /><span><strong>Empleado:</strong> marcación rápida, historial, alertas y boletas de pago.</span></li>
                <li><x-ui.icono name="equipo" :size="15" /><span><strong>Supervisor:</strong> panel del equipo asignado, solicitudes y reportes.</span></li>
                <li><x-ui.icono name="planilla" :size="15" /><span><strong>RR. HH.:</strong> personal, casos, nómina y reportes auditables.</span></li>
                <li><x-ui.icono name="escudo" :size="15" /><span><strong>Administrador:</strong> cuentas, permisos y auditoría de solo lectura.</span></li>
            </ul>
        </div>

        <div class="acceso__arte-pie">
            <div><strong>Monitoreo IoT</strong><span>Cámara por estación, sin audio y sin identificar rostros.</span></div>
            <div><strong>Nómina trazable</strong><span>Períodos, conceptos y boletas con historial de cambios.</span></div>
            <div><strong>Auditoría</strong><span>Acceso por rol verificado en el servidor, no solo en menús.</span></div>
        </div>
    </section>

    {{-- ---------------------------- formulario ----------------------------- --}}
    <section class="acceso__panel">
        <div class="acceso__caja">
            <h2>Iniciar sesión</h2>
            <p class="acceso__sub">Multiservicios EPAM E.I.R.L.</p>

            @if($errors->any())
                <div class="aviso aviso--error" style="margin-bottom:14px">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" novalidate>
                @csrf
                <div class="acceso__campo">
                    <label for="usuario">Usuario</label>
                    <div class="acceso__in">
                        <x-ui.icono name="usuario" :size="15" />
                        <input type="text" id="usuario" name="usuario" value="{{ old('usuario') }}"
                               placeholder="Ingrese su usuario" required autofocus autocomplete="username">
                    </div>
                </div>

                <div class="acceso__campo">
                    <label for="password">Contraseña</label>
                    <div class="acceso__in">
                        <x-ui.icono name="candado" :size="15" />
                        <input type="password" id="password" name="password" placeholder="Ingrese su contraseña"
                               required autocomplete="current-password">
                    </div>
                </div>

                <div class="acceso__fila">
                    <label class="v-switch">
                        <input type="checkbox" name="recordar" value="1"><span class="v-switch__pista"></span> Mantener la sesión
                    </label>
                    <a href="#" onclick="alert('Solicite el restablecimiento de su contraseña al Administrador del sistema desde su canal de soporte interno.'); return false" title="Solicite el restablecimiento al Administrador del sistema">¿Olvidó su contraseña?</a>
                </div>

                <button type="submit" class="acceso__btn">
                    Ingresar al sistema <x-ui.icono name="flecha" :size="15" />
                </button>
            </form>

            <p class="acceso__legal">Uso interno · El acceso a los módulos se valida por rol en el servidor. Las credenciales de prueba se entregan por el Administrador y deben cambiarse al primer ingreso.</p>
        </div>
    </section>
</div>
</body>
</html>
