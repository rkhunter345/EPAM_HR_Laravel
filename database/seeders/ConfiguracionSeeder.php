<?php

namespace Database\Seeders;

use App\Models\Sistema\Integracion;
use App\Models\Sistema\Parametro;
use App\Models\Sistema\PermisoRol;
use App\Models\User;
use Illuminate\Database\Seeder;

class ConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        $parametros = [
            // grupo, clave, etiqueta, valor, tipo, unidad, descripcion
            ['asistencia', 'tolerancia_ausencia', 'Tolerancia de ausencia en el puesto', '5', 'tiempo', 'min', 'Tiempo que debe transcurrir sin detectar presencia antes de registrar una alerta. Es una regla interna configurable, no una obligación legal.'],
            ['asistencia', 'refrigerio_min', 'Duración del refrigerio', '45', 'tiempo', 'min', 'Minutos de refrigerio aplicados cuando el calendario no define otro valor.'],
            ['asistencia', 'tolerancia_entrada', 'Tolerancia de entrada', '10', 'tiempo', 'min', 'Minutos de gracia antes de registrar una tardanza.'],
            ['asistencia', 'monitoreo_iot', 'Monitoreo de presencia (IoT)', '1', 'booleano', '', 'Habilita la recepción de eventos de las estaciones IoT.'],
            ['asistencia', 'deteccion_celular', 'Detección de uso de celular', '1', 'booleano', '', 'Registra el evento de uso de celular para revisión de RR. HH.; nunca genera sanciones automáticas.'],
            ['asistencia', 'salida_temporal_pausa', 'Pausar monitoreo en salida temporal', '1', 'booleano', '', 'Al registrar una salida temporal el monitoreo se detiene para no generar alertas de ausencia.'],
            ['seguridad', 'password_minimo', 'Longitud mínima de contraseña', '8', 'numero', 'caracteres', 'Aplica al cambio de contraseña de todos los perfiles.'],
            ['seguridad', 'sesion_minutos', 'Duración de la sesión', '120', 'tiempo', 'min', 'Tiempo de inactividad antes de cerrar la sesión.'],
            ['seguridad', 'intentos_login', 'Intentos de acceso permitidos', '5', 'numero', 'intentos', 'Bloqueo temporal tras superar el número de intentos fallidos.'],
            ['seguridad', 'auditoria_solo_lectura', 'Auditoría de solo lectura', '1', 'booleano', '', 'Los registros de auditoría no pueden editarse ni eliminarse desde la aplicación.'],
            ['notificacion', 'avisar_refrigerio', 'Avisar refrigerio sin marcar', '1', 'booleano', '', 'Envía un recordatorio al trabajador cuando inicia el horario de refrigerio.'],
            ['notificacion', 'avisar_jornada_abierta', 'Avisar jornada sin cerrar', '1', 'booleano', '', 'Recuerda marcar la salida al finalizar la jornada.'],
            ['notificacion', 'avisar_boleta', 'Avisar boleta disponible', '1', 'booleano', '', 'Notifica cuando la boleta de pago del periodo queda disponible.'],
            ['notificacion', 'avisar_casos', 'Avisar casos pendientes', '1', 'booleano', '', 'Avisa a RR. HH. y supervisores sobre solicitudes pendientes.'],
        ];

        foreach ($parametros as [$grupo, $clave, $etiqueta, $valor, $tipo, $unidad, $descripcion]) {
            Parametro::updateOrCreate(['clave' => $clave], [
                'grupo' => $grupo, 'etiqueta' => $etiqueta, 'valor' => $valor,
                'tipo' => $tipo, 'unidad' => $unidad ?: null, 'descripcion' => $descripcion,
            ]);
        }

        $integraciones = [
            ['Motor de lenguaje local (Ollama)', 'LLM', env('OLLAMA_URL', 'http://127.0.0.1:11434'), 'Asistente de RR. HH. · modelo '.env('OLLAMA_MODELO', 'llama3.2')],
            ['Estaciones IoT de presencia', 'IoT', 'POST /api/iot/evento', 'Recepción de eventos de ausencia y uso de celular con token de estación'],
            ['Servicio de correo', 'Correo', 'smtp://localhost:1025', 'Notificaciones de casos, boletas y alertas'],
            ['Generador de PDF', 'Documentos', 'dompdf', 'Boletas de pago y planilla en formato PDF'],
            ['Base de datos', 'Datos', 'sqlite://database/database.sqlite', 'Almacenamiento principal del sistema'],
        ];

        foreach ($integraciones as [$nombre, $tipo, $url, $detalle]) {
            Integracion::updateOrCreate(['nombre' => $nombre], [
                'tipo' => $tipo, 'url' => $url, 'detalle' => $detalle,
                'estado' => 'Conectado', 'activo' => true, 'ultima_sincronizacion' => now(),
            ]);
        }

        $modulos = [
            'Panel técnico', 'Usuarios y cuentas', 'Roles y permisos', 'Parámetros técnicos',
            'Asistencia y tolerancias', 'Catálogos', 'Integraciones', 'Auditoría',
            'Personal y contratos', 'Asistencia y correcciones', 'Casos y solicitudes',
            'Nómina', 'Boletas de pago', 'IoT y alertas', 'Reportes', 'Mi espacio del empleado',
        ];

        $permitidos = [
            'ADMIN' => ['Panel técnico', 'Usuarios y cuentas', 'Roles y permisos', 'Parámetros técnicos', 'Asistencia y tolerancias', 'Catálogos', 'Integraciones', 'Auditoría', 'Mi espacio del empleado'],
            'RRHH' => ['Personal y contratos', 'Asistencia y correcciones', 'Casos y solicitudes', 'Nómina', 'Boletas de pago', 'IoT y alertas', 'Reportes', 'Mi espacio del empleado'],
            'SUPERVISOR' => ['Asistencia y correcciones', 'Casos y solicitudes', 'Reportes', 'Mi espacio del empleado'],
            'EMPLEADO' => ['Mi espacio del empleado'],
        ];

        foreach (User::ROLES as $rol) {
            foreach ($modulos as $modulo) {
                PermisoRol::updateOrCreate(
                    ['rol' => $rol, 'modulo' => $modulo],
                    ['permitido' => in_array($modulo, $permitidos[$rol] ?? [], true)]
                );
            }
        }

        $this->command?->info('Configuración cargada: '.Parametro::count().' parámetros, '
            .Integracion::count().' integraciones, '.PermisoRol::count().' permisos por rol.');
    }
}
