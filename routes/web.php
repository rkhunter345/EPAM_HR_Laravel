<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Comun\PerfilController;
use App\Http\Controllers\Equipo\EquipoController;
use App\Http\Controllers\Espacio\EspacioController;
use App\Http\Controllers\Rrhh\RrhhController;
use App\Http\Controllers\Sistema\SistemaController;
use App\Http\Controllers\TableroController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| EPAM HR · v10 — rutas por rol (matriz de permisos del documento)
|--------------------------------------------------------------------------
| EMPLEADO   : su espacio de trabajo (jornada, monitoreo, alertas, casos, boletas)
| SUPERVISOR : su espacio + panel de supervisión del equipo asignado
| RRHH       : personal, asistencia, casos, nómina, boletas, reportes
| ADMIN      : configuración técnica, cuentas, roles, parámetros, auditoría
|
| FASE 4: no se registran rutas CRUD de prototipos antiguos (empleados,
| asistencias, justificaciones CRUD, permisos, vacaciones, periodos, reportes,
| estaciones IoT): sus controladores permanecen huérfanos y documentados. Sólo
| se registran las rutas de las pantallas realmente activas del producto.
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'form'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/', TableroController::class)->name('tablero');
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil');
    Route::put('/perfil', [PerfilController::class, 'actualizar'])->name('perfil.actualizar');
    Route::put('/perfil/contrasena', [PerfilController::class, 'contrasena'])->name('perfil.contrasena');

    // ------------------------------ Mi espacio ------------------------------
    Route::middleware('rol:EMPLEADO,SUPERVISOR,RRHH,ADMIN')->group(function () {
        Route::get('/mi-espacio', [EspacioController::class, 'inicio'])->name('espacio.inicio');
        Route::get('/mi-espacio/asistencia', [EspacioController::class, 'asistencia'])->name('espacio.asistencia');
        Route::post('/mi-espacio/marcar', [EspacioController::class, 'marcar'])->name('espacio.marcar');
        Route::get('/mi-espacio/monitoreo', [EspacioController::class, 'monitoreo'])->name('espacio.monitoreo');
        Route::get('/mi-espacio/historial', [EspacioController::class, 'historial'])->name('espacio.historial');
        Route::get('/mi-espacio/alertas', [EspacioController::class, 'alertas'])->name('espacio.alertas');
        Route::get('/mi-espacio/justificaciones', [EspacioController::class, 'justificaciones'])->name('espacio.justificaciones');
        Route::post('/mi-espacio/justificaciones', [EspacioController::class, 'guardarJustificacion'])->name('espacio.justificaciones.guardar');
        Route::get('/mi-espacio/justificaciones/{justificacion}/adjunto', [EspacioController::class, 'descargarJustificacion'])->name('espacio.justificaciones.adjunto');
        Route::get('/mi-espacio/vacaciones', [EspacioController::class, 'vacaciones'])->name('espacio.vacaciones');
        Route::post('/mi-espacio/vacaciones', [EspacioController::class, 'guardarVacaciones'])->name('espacio.vacaciones.guardar');
        Route::get('/mi-espacio/boletas', [EspacioController::class, 'boletas'])->name('espacio.boletas');
        Route::get('/mi-espacio/boletas/{detalle}/pdf', [\App\Http\Controllers\Nomina\MiNominaController::class, 'boleta'])->name('minomina.boleta');
        Route::post('/mi-espacio/emergencia', [EspacioController::class, 'reportarEmergencia'])->name('espacio.emergencia');
    });

    // ---------------------- Salida temporal (todos los roles) ----------------
    Route::middleware('rol:EMPLEADO,SUPERVISOR,RRHH')->group(function () {
        Route::resource('salidas', \App\Http\Controllers\Asistencias\SalidaTemporalController::class)->only(['index', 'store']);
        Route::post('salidas/{salida}/regresar', [\App\Http\Controllers\Asistencias\SalidaTemporalController::class, 'regresar'])->name('salidas.regresar');
        Route::get('salidas/{salida}/adjunto', [\App\Http\Controllers\Asistencias\SalidaTemporalController::class, 'descargarAdjunto'])->name('salidas.adjunto');
    });

    // ------------------------------- Supervisor ------------------------------
    Route::middleware('rol:SUPERVISOR')->group(function () {
        Route::get('/equipo', [EquipoController::class, 'panel'])->name('equipo.panel');
        Route::get('/equipo/asistencia', [EquipoController::class, 'asistencia'])->name('equipo.asistencia');
        Route::get('/equipo/monitoreo', [EquipoController::class, 'monitoreo'])->name('equipo.monitoreo');
        Route::get('/equipo/solicitudes', [EquipoController::class, 'solicitudes'])->name('equipo.solicitudes');
        Route::post('/equipo/revisar/{tipo}/{id}', [EquipoController::class, 'revisar'])->name('equipo.revisar');
        Route::get('/equipo/reportes', [EquipoController::class, 'reportes'])->name('equipo.reportes');
    });

    // --------------------------------- RR. HH. -------------------------------
    Route::middleware('rol:RRHH')->prefix('rrhh')->name('rrhh.')->group(function () {
        Route::get('/', [RrhhController::class, 'panel'])->name('panel');
        Route::get('/personal', [RrhhController::class, 'personal'])->name('personal');
        Route::get('/asistencia', [RrhhController::class, 'asistencia'])->name('asistencia');
        Route::post('/asistencia/{asistencia}/corregir', [RrhhController::class, 'corregir'])->name('asistencia.corregir');
        Route::get('/iot', [RrhhController::class, 'iot'])->name('iot');
        Route::get('/justificaciones', [RrhhController::class, 'justificaciones'])->name('justificaciones');
        Route::get('/justificaciones/{justificacion}/adjunto', [RrhhController::class, 'adjuntoJustificacion'])->name('justificaciones.adjunto');
        Route::get('/emergencias', [RrhhController::class, 'emergencias'])->name('emergencias');
        Route::get('/vacaciones', [RrhhController::class, 'vacaciones'])->name('vacaciones');
        Route::get('/nomina', [RrhhController::class, 'nomina'])->name('nomina');
        Route::get('/boletas', [RrhhController::class, 'boletas'])->name('boletas');
        Route::get('/reportes', [RrhhController::class, 'reportes'])->name('reportes');
        Route::post('/revisar/{tipo}/{id}', [RrhhController::class, 'revisar'])->name('revisar');
        Route::get('/boletas/{periodo}/{detalle}/pdf', [\App\Http\Controllers\Nomina\PeriodoController::class, 'boletaPdf'])->name('boleta.pdf');
        Route::get('/nomina/{periodo}/planilla', [\App\Http\Controllers\Nomina\PeriodoController::class, 'planilla'])->name('planilla');
    });

    // ------------------------------- Administrador ---------------------------
    Route::middleware('rol:ADMIN')->prefix('sistema')->name('sistema.')->group(function () {
        Route::get('/', [SistemaController::class, 'panel'])->name('panel');
        Route::get('/usuarios', [SistemaController::class, 'usuarios'])->name('usuarios');
        Route::post('/usuarios', [SistemaController::class, 'guardarUsuario'])->name('usuarios.guardar');
        Route::post('/usuarios/{usuario}/estado', [SistemaController::class, 'estadoUsuario'])->name('usuarios.estado');
        Route::get('/roles', [SistemaController::class, 'roles'])->name('roles');
        // FASE 2.A: la política de permisos es fija por rol; la matriz es
        // informativa y no editable, por lo que la ruta de guardado se retiró.
        Route::get('/parametros', [SistemaController::class, 'parametros'])->name('parametros');
        Route::post('/parametros', [SistemaController::class, 'guardarParametros'])->name('parametros.guardar');
        Route::get('/tolerancias', [SistemaController::class, 'tolerancias'])->name('tolerancias');
        Route::get('/catalogos', [SistemaController::class, 'catalogos'])->name('catalogos');
        Route::get('/integraciones', [SistemaController::class, 'integraciones'])->name('integraciones');
        Route::post('/integraciones/{integracion}/probar', [SistemaController::class, 'probarIntegracion'])->name('integraciones.probar');
        Route::get('/auditoria', [SistemaController::class, 'auditoria'])->name('auditoria');
    });

    // ----------------------------- Asistente (todos) -------------------------
    Route::get('/asistente', [\App\Http\Controllers\Asistente\AsistenteController::class, 'index'])->name('asistente.index');
    Route::post('/asistente', [\App\Http\Controllers\Asistente\AsistenteController::class, 'preguntar'])->name('asistente.preguntar');
    Route::post('/asistente/api', [\App\Http\Controllers\Asistente\AsistenteController::class, 'api'])->name('asistente.api');
});

// API para estaciones IoT (autenticadas por token, sin sesión web)
Route::post('/api/iot/evento', [\App\Http\Controllers\Sistema\IotController::class, 'recibirEvento'])->name('api.iot.evento');
