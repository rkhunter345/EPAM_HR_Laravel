# EPAM HR — Sistema web de gestión de asistencia y planilla

**Cliente:** MULTISERVICIOS EPAM E.I.R.L. (Huánuco, Perú)
**Stack:** Laravel 11 (MVC clásico) · PHP 8.2+ · SQLite/MySQL · Blade

---

## 1. Organización por módulos (lo primero que ves)

Cada módulo tiene **su carpeta de modelos, su carpeta de controladores, sus vistas y su bloque de rutas**
con el mismo nombre. Para encontrar cualquier cosa: busca el módulo y ahí está todo.

```
epam-hr/
├── app/
│   ├── Models/                        ← MODELOS (Eloquent) agrupados por módulo
│   │   ├── Empleados/                 Empleado.php
│   │   ├── Asistencias/               Asistencia.php · Justificacion.php · SolicitudVacaciones.php · SolicitudPermiso.php
│   │   ├── Nomina/                    PeriodoPlanilla.php · DetallePlanilla.php · ConceptoSalarial.php
│   │   ├── Organizacion/              Compania.php · Sucursal.php · Departamento.php · Designacion.php
│   │   │                              GradoEmpleado.php · GrupoEmpleados.php
│   │   ├── Configuracion/             TipoEmpleo.php · TipoContrato.php · CalendarioTrabajo.php
│   │   ├── Sistema/                   Auditoria.php · ReporteGenerado.php
│   │   ├── Concerns/TieneEtiqueta.php  trait con etiquetaCampo()/etiqueta()
│   │   └── User.php                   usuario del sistema (rol + empleado_id)
│   ├── Http/
│   │   ├── Controllers/               ← CONTROLADORES agrupados por módulo
│   │   │   ├── Empleados/             EmpleadoController.php
│   │   │   ├── Asistencias/           AsistenciaController.php · JustificacionController.php
│   │   │   │                          VacacionesController.php · PermisoController.php
│   │   │   ├── Nomina/                PeriodoController.php · ConceptoController.php
│   │   │   ├── Organizacion/          Compania/Sucursal/Departamento/Designacion/Grado/GrupoController.php
│   │   │   ├── Configuracion/         TipoEmpleo/TipoContrato/Calendario/UsuarioController.php
│   │   │   ├── Reportes/              ReporteController.php
│   │   │   ├── Asistente/             AsistenteController.php (chatbot RR.HH.)
│   │   │   ├── Auth/                  LoginController.php
│   │   │   ├── TableroController.php  tablero principal
│   │   │   ├── Controller.php         base de Laravel
│   │   │   └── ModuloCrudController.php  CRUD genérico para los maestros
│   │   └── Middleware/                VerificarRol.php · RegistrarAuditoria.php
│   ├── Services/                      AsistenciaService.php · PlanillaService.php (reglas de negocio)
│   └── Providers/AppServiceProvider.php
├── bootstrap/            app.php · providers.php
├── config/               app · auth · database · session · cache · filesystems · logging · view
├── database/
│   ├── migrations/       5 migraciones con claves foráneas
│   └── seeders/          DatabaseSeeder.php (datos demo)
├── public/               index.php · .htaccess · css/epam.css · (raíz del servidor)
├── resources/views/      ← VISTAS por módulo (index/create/edit/show)
│   ├── layouts/app.blade.php          layout: rail + sidebar + topbar
│   ├── components/ui/                 icono.blade.php · badge.blade.php
│   ├── components/crud/               index · form · show · paginacion
│   ├── auth/ dashboard/ empleados/ asistencias/ justificaciones/ vacaciones/ permisos/
│   ├── planilla/periodos · planilla/conceptos
│   ├── organizacion/{companias,sucursales,departamentos,designaciones,grados,grupos}
│   ├── configuracion/{tipos-empleo,tipos-contrato,calendarios,usuarios}
│   └── reportes/ asistente/
├── routes/web.php        ← RUTAS: un bloque por módulo (Route::resource)
├── artisan · composer.json · .env.example · .gitignore
└── README.md
```

> Los maestros (organización y configuración) heredan de `ModuloCrudController`, así que su controlador
> son ~20 líneas que declaran modelo, vista, ruta y campos. El módulo de personal, asistencia y nómina sí
> tiene lógica propia en su controlador + `app/Services`.

---

## 2. Mapa módulo → modelo / controlador / vistas / rutas

| Módulo | Modelo(s) | Controlador | Vistas | Rutas (nombre) |
|---|---|---|---|---|
| **Empleados** | `Empleados\Empleado` | `Empleados\EmpleadoController` | `empleados/index,create,edit,show,_formulario` | `Route::resource('empleados', …)` |
| **Asistencia** | `Asistencias\Asistencia` | `Asistencias\AsistenciaController` | `asistencias/index,create,edit,show` | `Route::resource('asistencias', …)` + `asistencias.marcar` |
| **Justificaciones** | `Asistencias\Justificacion` | `Asistencias\JustificacionController` | `justificaciones/*` | `asistencias.justificaciones.*` + `aprobar` |
| **Vacaciones** | `Asistencias\SolicitudVacaciones` | `Asistencias\VacacionesController` | `vacaciones/*` | `vacaciones.*` + `vacaciones.aprobar` |
| **Permisos** | `Asistencias\SolicitudPermiso` | `Asistencias\PermisoController` | `permisos/*` | `permisos.*` + `permisos.aprobar` |
| **Nómina** | `Nomina\PeriodoPlanilla`, `Nomina\DetallePlanilla` | `Nomina\PeriodoController` | `planilla/periodos/*` (+ `recibo`) | `periodos.*` + `periodos.calcular`, `periodos.recibo` |
| **Conceptos salariales** | `Nomina\ConceptoSalarial` | `Nomina\ConceptoController` | `planilla/conceptos/*` | `conceptos.*` |
| **Organización** | `Organizacion\Compania`, `Sucursal`, `Departamento`, `Designacion`, `GradoEmpleado`, `GrupoEmpleados` | `Organizacion\{Compania,Sucursal,Departamento,Designacion,Grado,Grupo}Controller` | `organizacion/{companias,sucursales,departamentos,designaciones,grados,grupos}/*` | `companias.*`, `sucursales.*`, `departamentos.*`, `designaciones.*`, `grados.*`, `grupos.*` |
| **Configuración** | `Configuracion\TipoEmpleo`, `TipoContrato`, `CalendarioTrabajo`, `User` | `Configuracion\{TipoEmpleo,TipoContrato,Calendario,Usuario}Controller` | `configuracion/{tipos-empleo,tipos-contrato,calendarios,usuarios}/*` | `tipos-empleo.*`, `tipos-contrato.*`, `calendarios.*`, `usuarios.*` (rol ADMIN) |
| **Reportes** | `Sistema\ReporteGenerado` | `Reportes\ReporteController` | `reportes/index,show` | `reportes.index`, `reportes.generar`, `reportes.show` |
| **Asistente** | — (consulta Asistencia/Planilla) | `Asistente\AsistenteController` | `asistente/index` | `asistente.index`, `asistente.preguntar` |
| **Tablero** | — | `TableroController` | `dashboard` | `tablero` |
| **Login** | `User` | `Auth\LoginController` | `auth/login` | `login`, `login.post`, `logout` |

---

## 3. Relaciones de los modelos

```
Compania 1──N Sucursal 1──N Departamento 1──N Designacion
Departamento 1──N Empleado        Designacion 1──N Empleado
GradoEmpleado 1──N Empleado       GrupoEmpleados 1──N Empleado
TipoEmpleo 1──N Empleado          TipoContrato 1──N Empleado
CalendarioTrabajo 1──N Empleado

Empleado 1──N Asistencia          (unique empleado_id + fecha)
Empleado 1──N Justificacion
Empleado 1──N SolicitudVacaciones
Empleado 1──N SolicitudPermiso
Empleado 1──N DetallePlanilla

PeriodoPlanilla 1──N DetallePlanilla N──1 Empleado
PeriodoPlanilla 1──N DetallePlanilla N──1 ConceptoSalarial (vía detalle)

User N──1 Empleado (opcional)     Justificacion N──1 User (revisado_por)
SolicitudVacaciones/Permiso N──1 User (aprobado_por)
ReporteGenerado N──1 User (generado_por)
```

Todas las relaciones están declaradas en el modelo (`belongsTo` / `hasMany`), y las claves foráneas
existen en las migraciones con `constrained()->nullOnDelete()`.

---

## 4. Instalación

Requisitos: **PHP ≥ 8.2**, extensiones `pdo_sqlite`, `mbstring`, `openssl`, y **Composer 2**.

```bash
composer install
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate
php artisan migrate --seed    # crea database/database.sqlite y carga datos demo
php artisan serve             # http://127.0.0.1:8000
```

Para MySQL: en `.env` cambia `DB_CONNECTION=mysql`, `DB_DATABASE=epam_hr`, `DB_USERNAME`, `DB_PASSWORD`
y crea la base antes de `php artisan migrate --seed`.

### Usuarios demo (contraseña `epam123`)

| Usuario | Rol |
|---|---|
| `admin` | ADMIN — acceso total |
| `rrhh` | RRHH — personal, asistencia, nómina |
| `supervisor` | SUPERVISOR — asistencia de su área |
| `angela`, `juan`, `maria`, `carlos`, `lucia` | EMPLEADO — solo sus datos |

---

## 5. Reglas de negocio (app/Services)

- **AsistenciaService**: jornada de 480 min; tolerancia de 10 min del calendario; refrigerio de 45 min que
  **no** computa como trabajo; estados `Asistió / Tardanza / Ausente / Justificado / Incompleto`; cálculo de
  minutos de tardanza, exceso de refrigerio y minutos trabajados; resumen por período y % de puntualidad.
- **PlanillaService** (motor de planilla Perú 2026, tasas en `config/planilla.php`):
  1) Ingresos: básico ≥ RMV + asignación familiar (10 % RMV) + horas extras (primeras 2 h +25 %, desde la 3.ª +35 %) + bono de asistencia perfecta.
  2) Descuentos del trabajador: tardanzas/faltas + pensión (ONP 13 % o AFP 10 % + comisión sobre flujo + prima 1.37 %) + renta de 5.ª (7 UIT exoneradas, tramos anuales 5/14/17/20/30 %, retención mensual = anual/12).
  3) Aportes del empleador: EsSalud 9 % y SCTR 1.25 %.
  4) Costo empresa = total bruto + aportes del empleador. Hoja A4 imprimible en `Planilla A4`.

---

## 6. Diseño (según las pantallas de referencia del PDF)

- Doble barra lateral: **rail de iconos de 48 px** + **sidebar de 212 px** con grupos colapsables.
- Topbar de 50 px con migas de pan, buscador, campana y avatar.
- Fondo `#f5f6f8`, tarjetas blancas con borde `#e6e8ec`, acento azul `#2490ef`.
- Badges de estado: verde `#29cd42`, ámbar `#ffa00a`, rojo `#f5455c`, violeta `#7c5cf5`.
- Formulario de empleado con **stepper** de 4 pasos (Información básica → Datos del empleo → Datos
  personales → Documentos), igual que la pantalla de alta del PDF.
- KPIs del listado de empleados: Total, Nuevos este año, En proceso, Salidas este año.

Todo el CSS está en `public/css/epam.css` (sin frameworks externos, sin CDN).

---

## 7. Tasas oficiales 2026 (config/planilla.php)

| Parámetro | Valor 2026 | Fuente |
|---|---|---|
| RMV | S/ 1,230.00 (desde 01/10/2026) | D.S. que incrementa la RMV (El Peruano) |
| UIT | S/ 5,500.00 | SUNAT, D.S. 301-2025-EF |
| ONP | 13 % del bruto | D.L. 19990 |
| AFP | 10 % + comisión flujo (Habitat 1.47 %, Integra 1.55 %, Prima 1.60 %, Profuturo 1.69 %) + prima de seguro 1.37 % | SBS 2026 |
| Asignación familiar | 10 % de la RMV (S/ 123.00) | D.L. 25129 |
| Horas extras | +25 % primeras 2 h; +35 % desde la 3.ª | R.S. 669-97-TR |
| EsSalud | 9 % (básico + asignación familiar) | D.L. 19990 |
| SCTR | 1.25 % (configurable por rubro) | D.S. 009-97-SA |
| Renta 5.ª | 7 UIT exoneradas; tramos 5/14/17/20 % + 30 % de exceso | SUNAT |

---

## 8. Matriz de roles y permisos (v4)

Validación en el **servidor** con el middleware `rol:` en cada ruta (no basta con ocultar botones en JS).
El menú lateral también se filtra por rol.

| Módulo / acción | ADMIN | RR.HH. | SUPERVISOR | EMPLEADO |
|---|:-:|:-:|:-:|:-:|
| Tablero (resumen) | ✔ | ✔ | ✔ | ✔ |
| Empleados (CRUD completo) | ✔ | ✔ | — | — |
| Marcaciones: registrar / editar / marcar entrada-refrigerio-salida | ✔ | ✔ | — | — |
| Marcaciones: ver el listado | ✔ (todas) | ✔ (todas) | ✔ (todas) | ✔ (solo las suyas) |
| Justificaciones: crear la suya / ver | ✔ | ✔ | ✔ | ✔ (solo las suyas) |
| Justificaciones: editar / eliminar | ✔ | ✔ | — | — |
| Justificaciones: **aprobar / rechazar** | ✔ | ✔ | ✔ | — |
| Vacaciones: solicitar / ver | ✔ | ✔ | ✔ | ✔ (solo las suyas) |
| Vacaciones: **aprobar** | ✔ | ✔ | ✔ | — |
| Permisos: solicitar / ver | ✔ | ✔ | ✔ | ✔ (solo las suyas) |
| Permisos: **aprobar** | ✔ | ✔ | ✔ | — |
| Planilla de remuneraciones (ver, calcular, períodos) | ✔ | ✔ | — | — |
| **Planilla general de todos los empleados (PDF A4)** | ✔ | ✔ | — | — |
| Planilla general vista HTML A4 | ✔ | ✔ | — | — |
| Conceptos salariales | ✔ | ✔ | — | — |
| Boleta de pago individual (PDF, plantilla peruana) | ✔ | ✔ | — | — |
| **Mi nómina** (sus propias boletas + Boleta PDF) | — | — | — | ✔ |
| Reportes (generar / ver) | ✔ | ✔ | ✔ | — |
| Monitoreo IoT (estaciones, eventos, API) | ✔ | ✔ | — | — |
| Organización (compañía, sucursal, depto, designación, grado, grupo) | ✔ | ✔ | — | — |
| Tipo de empleo / contrato / calendario | ✔ | ✔ | — | — |
| Usuarios y roles | ✔ | — | — | — |
| Asistente RR.HH. (robot flotante + página) | ✔ | ✔ | ✔ | ✔ (solo sus datos) |

## 9. Asistente inteligente (Ollama)

- Botón **robot flotante** abajo a la derecha, en todas las pantallas. Al hacer clic se despliega un
  panel de chat que entra **desde la derecha** (animación), con Escape o ✕ para cerrar.
- Backend: `app/Services/OllamaService.php` llama a `POST {OLLAMA_URL}/api/chat` (por defecto
  `http://127.0.0.1:11434`) con el modelo de `OLLAMA_MODELO` (por defecto `llama3.2`).
- Si Ollama no está disponible, el asistente responde con el motor local de reglas e indica
  «Motor: local (Ollama no disponible)». Nunca inventa cifras: sólo usa datos de la base.
- El contexto enviado al modelo depende del rol: el EMPLEADO solo recibe sus datos; SUPERVISOR un
  resumen; ADMIN/RRHH el resumen general y la ficha del empleado consultado.
- No aprueba solicitudes ni modifica registros (como exige la tesis).

Para activarlo con Ollama instalado en tu PC:

```powershell
ollama pull llama3.2
ollama serve        # normalmente ya queda corriendo en 127.0.0.1:11434
```

## 10. Módulo IoT (andamio, listo para entrenar)

- Tablas: `iot_estaciones` (cámara o lector QR por puesto, empleado asignado, último ping) y
  `iot_eventos` (ausencia_puesto, uso_celular, regreso, marcacion_qr; duración, estado Registrado/
  Revisado/Descartado).
- Pantalla **Monitoreo IoT** (menú Configuración, roles ADMIN/RRHH) con KPIs del día, tabla de
  estaciones y últimos eventos con acciones Revisado/Descartar.
- API para dispositivos: `POST /api/iot/evento` con cabecera `X-IOT-TOKEN` (configurable en
  `.env` como `IOT_TOKEN`, exento de CSRF). Ejemplo:

```bash
curl -X POST http://127.0.0.1:8000/api/iot/evento \
  -H "Content-Type: application/json" \
  -H "X-IOT-TOKEN: epam-iot-2026" \
  -d '{"estacion":"estacion-1","empleado_codigo":"HR-EMP-00001","tipo":"ausencia_puesto","duracion_min":5}'
```

Respuesta: `{"ok":true,"evento_id":1,"empleado":"Angela Cruz Ramos","estacion":"estacion-1"}`.
La detección (visión artificial / entrenamiento del modelo) se conectará después: el módulo ya
recibe, almacena, muestra y permite auditar los eventos.

## 11. Nómina en PDF (barryvdh/laravel-dompdf)

- **Boleta de pago individual** — réplica de la plantilla peruana `Boleta_de_Pago_Peru.xlsx`
  (razón social + RUC, BOLETA DE PAGO y mes, código/fecha de ingreso/trabajador/tipo, DNI/régimen
  laboral, cargo/régimen pensionario, área/días laborados, dos columnas INGRESOS y
  DESCUENTOS/APORTES, bloque de APORTACIONES DEL EMPLEADOR con EsSalud 9 % y SCTR 1.25 %, banda
  NETO A PAGAR, pie legal T-Registro/PLAME y firmas). Descarga: botón «Boleta PDF» en cada fila.
- **Planilla de Remuneraciones general** — hoja A4 con todos los empleados del período (la misma
  estructura de la vista web) en PDF descargable con «Planilla PDF».

---

## 12. Novedades v7

- **Reportes en A4**: la impresión del navegador ya fuerza hoja A4 vertical (`@page size: A4 portrait`);
  el botón «A4 horizontal» abre la misma tabla en hoja apaisada para muchos registros; «Excel (CSV)»
  descarga con codificación UTF-8 (abre directo en Excel). Las tres salidas usan el MISMO filtro de fechas.
- **Filtro de fechas corregido**: antes un rango 25–27 excluía el día 27 (el guardado era datetime
  `2026-10-27 00:00:00`, fuera de `between` del día). Ahora `whereDate >= desde` y `<= hasta`,
  inclusive en ambos extremos, en pantalla, CSV y horizontal. Verificado: 25–27 muestra solo 25, 26 y 27.
- **Cámara IoT**: botón «Encender cámara» / «Apagar cámara» con `getUserMedia`; muestra el estado,
  el nombre del dispositivo y errores claros (permiso denegado, sin cámara, cámara ocupada).
  Funciona en `http://127.0.0.1` y en **https**; la imagen no se graba ni se envía.
- **Mi nómina (rol EMPLEADO)**: módulo propio con sus boletas por período y descarga individual de
  su boleta en PDF. Autorización por el `empleado_id` vinculado al usuario (intentar ver una boleta
  ajena devuelve 403). El menú lateral del empleado muestra solo: Inicio, Marcaciones,
  Justificaciones, Vacaciones, Permisos, **Mi nómina** y Asistente — nada más.
