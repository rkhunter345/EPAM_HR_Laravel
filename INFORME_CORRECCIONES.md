# INFORME DE CORRECCIONES — EPAM HR (Laravel 11)

Correcciones aplicadas conforme al documento de auditoría. No se ejecutaron
migraciones, no se tocó `.env`, la base SQLite real, sesiones, credenciales,
redes externas ni el diseño corporativo (CSS/logotipo/menú intactos).

## Cambios por fase

### FASE 2.A — Matriz de roles/permisos
- La matriz `PermisoRol` era editable en la interfaz pero el middleware `rol`
  comparaba listas estáticas: activar/desactivar casillas no cambiaba el acceso real.
- Estrategia elegida (documentada): política FIJA por rol. La autorización la
  aplica el middleware `rol` en el servidor. La matriz ahora es informativa y
  NO editable (`sistema/roles.blade.php`); la ruta `sistema.roles.guardar` se
  retiró y `guardarRol()` se eliminó. Un ADMIN no puede otorgarse acceso a
  datos de negocio desde la interfaz.

### FASE 2.B — Alcance de supervisores
- `ResuelveEmpleado::equipoDelSupervisor()` ahora resuelve el equipo SOLO por
  clave estable (`empleados.departamento_id` del expediente del supervisor).
- Fallo seguro: sin expediente/departamento devuelve colección VACÍA (nunca
  toda la plantilla). Se eliminó la comparación frágil entre
  `departamentos.responsable` y el nombre visible del usuario.

### FASE 2.C — Revisiones del supervisor
- `EquipoController::revisar` ahora verifica que el caso pertenezca al equipo
  asignado (403 si no), además del bloqueo de casos propios ya existente.

### FASE 2.D — Salidas temporales (IDOR)
- El EMPLEADO ya no puede elegir `empleado_id`; siempre se deriva de su sesión.
- El SUPERVISOR ya no registra/cierra salidas de terceros (solo las suyas).
  Solo RR. HH. tiene alcance de gestión documentado.
- `regresar` valida pertenencia antes de actualizar (403 si no corresponde).

### FASE 2.E — Asistente virtual
- El contexto de RRHH/ADMIN ya no incluye fichas de empleados ni salarios.
- ADMIN no recibe registros laborales ni de nómina.
- SUPERVISOR recibe solo resumen AGREGADO de su equipo, sin sueldos ni nómina.
- La ficha de empleado nunca incluye salario ni neto de planilla.
- El motor local (fallback) respeta el mismo alcance: un EMPLEADO solo ve su
  propia boleta publicada; nadie consulta fichas de terceros por la pregunta.

### FASE 2.F — API IoT
- Autenticación fail-safe: si `IOT_TOKEN` está vacío o es el marcador de
  ejemplo, la API responde 503/401 y no crea eventos (el valor publicado
  `epam-iot-2026` se retiró de código y de `.env.example`).
- Solo estaciones ACTIVAS pueden enviar eventos.
- La respuesta ya no devuelve nombres personales (solo `empleado_identificado`).

### FASE 2.G — Login y cuentas
- Throttling de inicio de sesión: 5 intentos por usuario+IP por minuto.
- Se retiró el bloque de contraseñas de demostración de la vista de login y el
  enlace "¿Olvidó su contraseña?" ya no es falso (mensaje claro de solicitud al
  Administrador).
- Creación de cuenta: sin contraseña temporal fija codificada (`epam2026`);
  ahora se genera una temporal aleatoria de un solo uso que se entrega por el
  canal seguro del Administrador.
- No se puede desactivar la última cuenta ADMIN activa.

### FASE 2.H — Relación User–Empleado
- Nueva migración INCREMENTAL y REVERSIBLE
  `2026_11_10_000001_integridad_users_empleado.php`: añade unicidad y FK de
  `users.empleado_id` hacia `empleados.id`. ANTES de modificar el esquema
  genera un informe de cuentas huérfanas y vínculos duplicados y DETIENE la
  migración sin tocar datos si existen inconsistencias.
- PENDIENTE de decisión del propietario: campos legacy `id_usuario` y
  `crear_usuario` de `empleados` (no se eliminaron para no romper formularios;
  documentar su retiro en una etapa posterior).

### FASE 3 — Boletas del empleado
- `espacio/boletas.blade.php` apuntaba a la ruta protegida solo RRHH
  (`rrhh.boleta.pdf`): el empleado recibía 403 al descargar su boleta.
  Ahora usa la ruta de empleado `minomina.boleta` → `MiNominaController::boleta`,
  registrada bajo el grupo de "Mi espacio".
- `MiNominaController::boleta` valida: boleta propia, detalle correspondiente
  al período y período NO en borrador. La ruta de RRHH se mantiene separada y
  también bloquea boletas en borrador.

### FASE 4 — Rutas y código huérfano
- Se verificó cada `route()` de las vistas activas contra `routes/web.php`.
- Se corrigió el error de tipografía en `JustificacionController::store`
  («ustificacion::create» → «Justificacion::create»), aunque el controlador
  permanece SIN rutas (documentado como huérfano; no se conectó por accidente).
- No se registraron rutas CRUD de prototipos antiguos (empleados, asistencias,
  justificaciones CRUD, permisos, vacaciones, periodos, reportes, estaciones IoT).
- `probarIntegracion` ya NO marca "Conectado" sin comprobar nada: registra
  «Sin verificar» y queda auditado como simulación de interfaz.

### FASE 5 — Privacidad de adjuntos
- Justificaciones y documentos de salidas temporales se guardan ahora en el
  disco PRIVADO `expedientes` (`storage/app/expedientes`), no en `public`.
- Nuevas rutas de descarga AUTENTICADAS con control de propiedad/rol:
  `espacio.justificaciones.adjunto`, `rrhh.justificaciones.adjunto`,
  `salidas.adjunto`. Las vistas ya no enlazan con `asset()` público.

## Archivos modificados
- routes/web.php (rutas por rol + nuevas rutas de boleta/adjuntos)
- app/Http/Controllers/Concerns/ResuelveEmpleado.php
- app/Http/Controllers/Equipo/EquipoController.php
- app/Http/Controllers/Asistencias/SalidaTemporalController.php
- app/Http/Controllers/Asistencias/JustificacionController.php
- app/Http/Controllers/Espacio/EspacioController.php
- app/Http/Controllers/Rrhh/RrhhController.php
- app/Http/Controllers/Nomina/MiNominaController.php
- app/Http/Controllers/Nomina/PeriodoController.php
- app/Http/Controllers/Sistema/SistemaController.php
- app/Http/Controllers/Sistema/IotController.php
- app/Http/Controllers/Auth/LoginController.php
- app/Http/Controllers/Asistente/AsistenteController.php
- config/epam.php · .env.example
- resources/views/auth/login.blade.php
- resources/views/espacio/boletas.blade.php · espacio/justificaciones.blade.php
- resources/views/rrhh/justificaciones.blade.php
- resources/views/salidas/index.blade.php
- resources/views/sistema/roles.blade.php

## Archivos añadidos
- database/migrations/2026_11_10_000001_integridad_users_empleado.php
- phpunit.xml · tests/TestCase.php · tests/Feature/AutorizacionTest.php
- INFORME_CORRECCIONES.md (este archivo)

## Este ZIP NO incluye (por seguridad)
`.env`, `database/database.sqlite`, `vendor/`, sesiones/cache/logs de
`storage/framework`, `public/uploads` y documentos. Recupérelos de su copia
local o ejecute `composer install`.

## Qué debe ejecutar el propietario en casa (sin migrate:fresh)
```
composer install
php artisan migrate          # incremental: aplica solo la nueva migración
php artisan test             # suite de autorización (SQLite en memoria)
php artisan serve
```
Antes de `migrate`, revise el informe que imprime si existen cuentas huérfanas
o vínculos duplicados; corrija los vínculos y vuelva a intentarlo. Sus datos no
se modifican automáticamente.

## Verificaciones locales pendientes (no probadas aquí)
- Descarga de boleta propia (PDF) con un período publicado.
- Descarga de adjuntos (justificación/salida) con usuario y rol correctos.
- Evento IoT con token real generado por usted (`bin2hex(random_bytes(16))`).
- Capturas para la tesis con datos sintéticos (ver guía del documento, FASE 7).
