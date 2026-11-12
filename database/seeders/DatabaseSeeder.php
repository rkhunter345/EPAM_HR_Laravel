<?php

namespace Database\Seeders;

use App\Models\Asistencias\Asistencia;
use App\Models\Configuracion\CalendarioTrabajo;
use App\Models\Organizacion\Compania;
use App\Models\Nomina\ConceptoSalarial;
use App\Models\Organizacion\Departamento;
use App\Models\Organizacion\Designacion;
use App\Models\Empleados\Empleado;
use App\Models\Organizacion\GradoEmpleado;
use App\Models\Organizacion\GrupoEmpleados;
use App\Models\Asistencias\Justificacion;
use App\Models\Nomina\PeriodoPlanilla;
use App\Models\Asistencias\SolicitudPermiso;
use App\Models\Asistencias\SolicitudVacaciones;
use App\Models\Organizacion\Sucursal;
use App\Models\Configuracion\TipoContrato;
use App\Models\Configuracion\TipoEmpleo;
use App\Models\User;
use App\Services\AsistenciaService;
use App\Services\PlanillaService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Datos de demostración de MULTISERVICIOS EPAM E.I.R.L.
 * Ejecutar con: php artisan migrate:fresh --seed
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Idempotencia: vaciar tablas demo para permitir ejecutar el seeder varias veces
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        foreach ([
            'salidas_temporales',
            'iot_eventos',
            'iot_estaciones',
            'auditorias',
            'reportes_generados',
            'conceptos_salariales',
            'detalles_planilla',
            'periodos_planilla',
            'solicitudes_permiso',
            'solicitudes_vacaciones',
            'justificaciones',
            'asistencias',
            'empleados',
            'calendarios_trabajo',
            'tipos_contrato',
            'tipos_empleo',
            'grupos_empleados',
            'grados_empleado',
            'designaciones',
            'departamentos',
            'sucursales',
            'companias',
            'sessions',
            'users',
        ] as $tablaDemo) {
            if (\Illuminate\Support\Facades\Schema::hasTable($tablaDemo)) {
                \Illuminate\Support\Facades\DB::table($tablaDemo)->delete();
            }
        }
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // ------------------------------ Usuarios ------------------------------
        $usuarios = [
            ['name' => 'Administrador del sistema', 'usuario' => 'admin', 'email' => 'admin@epam.pe', 'rol' => 'ADMIN'],
            ['name' => 'Rosa Quispe (RR.HH.)', 'usuario' => 'rrhh', 'email' => 'rrhh@epam.pe', 'rol' => 'RRHH'],
            ['name' => 'Jorge Salazar (Supervisor)', 'usuario' => 'supervisor', 'email' => 'supervisor@epam.pe', 'rol' => 'SUPERVISOR'],
        ];
        foreach ($usuarios as $u) {
            User::updateOrCreate(['usuario' => $u['usuario']], $u + ['password' => Hash::make('epam123')]);
        }

        // ---------------------------- Organización ----------------------------
        $compania = Compania::create([
            'nombre' => 'MULTISERVICIOS EPAM E.I.R.L.', 'ruc' => '20481234567', 'moneda' => 'PEN',
            'telefono' => '(062) 512345', 'correo' => 'contacto@epam.pe',
            'direccion' => 'Jr. Dos de Mayo 480, Huánuco',
        ]);
        $sucursal = Sucursal::create(['compania_id' => $compania->id, 'nombre' => 'Sede Huánuco',
            'ciudad' => 'Huánuco', 'direccion' => 'Jr. Dos de Mayo 480', 'responsable' => 'Rosa Quispe']);

        $deptos = [];
        foreach (['Administración', 'Soporte Técnico', 'Mantenimiento', 'Contabilidad', 'Ventas'] as $i => $nombre) {
            $deptos[$nombre] = Departamento::create([
                'sucursal_id' => $sucursal->id, 'nombre' => $nombre,
                'responsable' => ['Rosa Quispe', 'Luis Reyes', 'Carlos Gómez', 'Ana Vega', 'María Ponce'][$i],
                'descripcion' => 'Área de '.mb_strtolower($nombre).' de la empresa.',
            ]);
        }

        $designaciones = [];
        foreach ([
            'Administración' => ['Jefe de RR.HH.', 'Asistente administrativo', 'Recepcionista'],
            'Soporte Técnico' => ['Técnico de soporte', 'Analista de sistemas', 'Jefe de soporte'],
            'Mantenimiento' => ['Técnico de mantenimiento', 'Supervisor de campo'],
            'Contabilidad' => ['Contador general', 'Asistente contable'],
            'Ventas' => ['Ejecutivo de ventas', 'Coordinador comercial'],
        ] as $depto => $puestos) {
            foreach ($puestos as $puesto) {
                $designaciones[$puesto] = Designacion::create([
                    'departamento_id' => $deptos[$depto]->id, 'nombre' => $puesto,
                    'descripcion' => $puesto.' del área de '.$depto.'.',
                ]);
            }
        }

        $grados = [];
        foreach (['Grado 1' => 1, 'Grado 2' => 2, 'Grado 3' => 3] as $nombre => $nivel) {
            $grados[$nombre] = GradoEmpleado::create(['nombre' => $nombre, 'nivel' => $nivel, 'descripcion' => 'Nivel jerárquico '.$nivel]);
        }
        $grupos = [];
        foreach (['Grupo A - Campo', 'Grupo B - Oficina'] as $g) {
            $grupos[$g] = GrupoEmpleados::create(['nombre' => $g, 'descripcion' => 'Agrupación para reportes de asistencia.']);
        }
        $tipoEmpleo = [];
        foreach (['Tiempo completo', 'Medio tiempo', 'Locación de servicios'] as $t) {
            $tipoEmpleo[$t] = TipoEmpleo::create(['nombre' => $t, 'descripcion' => 'Régimen de '.mb_strtolower($t).'.']);
        }
        $tipoContrato = [];
        foreach (['Indefinido' => null, 'Plazo fijo' => 12, 'Por obra o servicio' => 6] as $t => $meses) {
            $tipoContrato[$t] = TipoContrato::create(['nombre' => $t, 'duracion_meses' => $meses, 'descripcion' => 'Contrato '.mb_strtolower($t).'.']);
        }
        $calendario = CalendarioTrabajo::create(['nombre' => 'Jornada EPAM Lun-Vie',
            'dias_laborables' => 'Lun-Vie', 'hora_entrada' => '08:00:00', 'hora_salida' => '17:00:00',
            'tolerancia_min' => 10, 'refrigerio_min' => 45]);

        // ------------------------------ Empleados -----------------------------
        $planilla = [
            ['Angela', 'Cruz', 'Ramos', 'Femenino', 'Soporte Técnico', 'Técnico de soporte', 1850, 'Activo', 'Grado 2'],
            ['Juan', 'Pérez', 'López', 'Masculino', 'Administración', 'Asistente administrativo', 1700, 'Activo', 'Grado 1'],
            ['María', 'Huaytalla', 'Rojas', 'Femenino', 'Ventas', 'Ejecutivo de ventas', 1600, 'En proceso', 'Grado 1'],
            ['Carlos', 'Gómez', 'Díaz', 'Masculino', 'Contabilidad', 'Contador general', 2600, 'Activo', 'Grado 3'],
            ['Lucía', 'Paredes', 'Soto', 'Femenino', 'Administración', 'Jefe de RR.HH.', 3200, 'Inactivo', 'Grado 3'],
            ['Luis', 'Reyes', 'Mendoza', 'Masculino', 'Soporte Técnico', 'Jefe de soporte', 2900, 'Activo', 'Grado 3'],
            ['Rosa', 'Quispe', 'Ayala', 'Femenino', 'Administración', 'Recepcionista', 1300, 'Activo', 'Grado 1'],
            ['Pedro', 'Flores', 'Vargas', 'Masculino', 'Mantenimiento', 'Técnico de mantenimiento', 1750, 'Activo', 'Grado 2'],
            ['Ana', 'Vega', 'Castro', 'Femenino', 'Contabilidad', 'Asistente contable', 1500, 'Activo', 'Grado 1'],
            ['Marco', 'Tello', 'Núñez', 'Masculino', 'Mantenimiento', 'Supervisor de campo', 2100, 'Activo', 'Grado 2'],
            ['María', 'Ponce', 'León', 'Femenino', 'Ventas', 'Coordinador comercial', 2300, 'Activo', 'Grado 2'],
            ['Jorge', 'Salazar', 'Ibarra', 'Masculino', 'Soporte Técnico', 'Analista de sistemas', 2400, 'Activo', 'Grado 2'],
            ['Sofía', 'Ríos', 'Campos', 'Femenino', 'Administración', 'Asistente administrativo', 1450, 'En proceso', 'Grado 1'],
            ['Diego', 'Alvarado', 'Peña', 'Masculino', 'Soporte Técnico', 'Técnico de soporte', 1800, 'Activo', 'Grado 1'],
        ];

        $empleados = [];
        $n = 1;
        foreach ($planilla as [$p1, $p2, $ap, $genero, $depto, $puesto, $sueldo, $estado, $grado]) {
            $empleados[] = Empleado::create([
                'codigo' => 'HR-EMP-'.str_pad((string) $n, 5, '0', STR_PAD_LEFT),
                'primer_nombre' => $p1, 'segundo_nombre' => $p2, 'apellido' => $ap,
                'nombre_completo' => "{$p1} {$p2} {$ap}",
                'genero' => $genero, 'fecha_nacimiento' => Carbon::now()->subYears(24 + $n % 12)->toDateString(),
                'estado' => $estado, 'compania_id' => $compania->id, 'sucursal_id' => $sucursal->id,
                'departamento_id' => $deptos[$depto]->id, 'designacion_id' => $designaciones[$puesto]->id,
                'grado_empleado_id' => $grados[$grado]->id,
                'grupo_id' => $grupos[$n % 2 ? 'Grupo B - Oficina' : 'Grupo A - Campo']->id,
                'tipo_empleo_id' => $tipoEmpleo['Tiempo completo']->id,
                'tipo_contrato_id' => $tipoContrato[$n % 3 ? 'Plazo fijo' : 'Indefinido']->id,
                'calendario_id' => $calendario->id,
                'salario_mensual' => $sueldo, 'moneda_salario' => 'PEN', 'tipo_salario' => 'Mensual',
                'frecuencia_pago' => 'Mensual', 'metodo_pago' => 'Transferencia bancaria',
                'asignacion_familiar' => $n % 3 === 0,
                'afp' => ['ONP', 'Habitat', 'Integra', 'Prima', 'Profuturo'][$n % 5],
                'cuenta_bancaria' => 'BCP-'.$n.'-0'.(100 + $n),
                'jornada_laboral' => 'Tiempo completo', 'horas_semanales' => 48,
                'proyecto_cliente' => $n % 3 ? 'Soporte corporativo' : 'Mantenimiento institucional',
                'fecha_ingreso' => Carbon::now()->subMonths(14 - $n % 12)->toDateString(),
                'fecha_inicio' => Carbon::now()->subMonths(14 - $n % 12)->toDateString(),
                'fecha_confirmacion' => Carbon::now()->subMonths(11 - $n % 10)->toDateString(),
                'tipo_documento' => 'DNI', 'numero_documento' => '7'.str_pad((string) (1000000 + $n * 731), 8, '0', STR_PAD_LEFT),
                'estado_civil' => $n % 3 ? 'Soltero(a)' : 'Casado(a)',
                'telefono' => '9'.str_pad((string) (50000000 + $n * 137711), 8, '0', STR_PAD_LEFT),
                'correo_personal' => mb_strtolower($p1).'.'.mb_strtolower($ap).'@gmail.com',
                'direccion' => 'Jr. '.['Alameda', 'Huallayco', 'Tarapacá', 'General Prado'][$n % 4].' '.($n * 37 + 120),
                'distrito' => ['Huánuco', 'Amarilis', 'Pillco Marca'][$n % 3],
                'contacto_emergencia' => 'Familiar de '.$p1,
                'telefono_emergencia' => '9'.str_pad((string) (60000000 + $n * 91111), 8, '0', STR_PAD_LEFT),
            ]);
            $n++;
        }

        // Usuarios de ejemplo vinculados a empleados
        foreach (['angela' => 0, 'juan' => 1, 'maria' => 2, 'carlos' => 3, 'lucia' => 4] as $usuario => $indice) {
            User::updateOrCreate(['usuario' => $usuario], [
                'name' => $empleados[$indice]->nombre_completo,
                'email' => $usuario.'@epam.pe',
                'rol' => 'EMPLEADO',
                'empleado_id' => $empleados[$indice]->id,
                'password' => Hash::make('epam123'),
            ]);
        }

        // ----------------------------- Asistencias ----------------------------
        $servicio = app(AsistenciaService::class);
        $inicio = Carbon::now()->subDays(75)->startOfDay();

        for ($dia = 0; $dia < 76; $dia++) {
            $fecha = $inicio->copy()->addDays($dia);
            if ($fecha->isWeekend()) {
                continue;
            }
            foreach ($empleados as $i => $empleado) {
                // ~8 % de ausencias y ~22 % de tardanzas para que el tablero tenga datos
                $semilla = ($i * 7 + $dia * 13) % 100;
                if ($semilla < 8) {
                    $estado = 'Ausente';
                    $entrada = null;
                } else {
                    $minutos = $semilla < 30 ? 8 + ($semilla % 26) : -12 + ($semilla % 12);
                    $entrada = $fecha->copy()->setTime(8, 0)->addMinutes($minutos);
                    $estado = null;
                }

                $asistencia = new Asistencia([
                    'empleado_id' => $empleado->id,
                    'fecha' => $fecha->toDateString(),
                    'hora_entrada' => $entrada?->format('H:i:s'),
                    'hora_inicio_refrigerio' => $entrada ? $fecha->copy()->setTime(13, 0)->format('H:i:s') : null,
                    'hora_fin_refrigerio' => $entrada ? $fecha->copy()->setTime(13, 45)->format('H:i:s') : null,
                    'hora_salida' => $entrada ? $fecha->copy()->setTime(17, 0)->addMinutes($semilla % 18)->format('H:i:s') : null,
                    'origen' => ['Web', 'Móvil', 'QR'][$semilla % 3],
                    'estado' => $estado ?? 'Asistió',
                ]);
                $asistencia->empleado_id = $empleado->id;
                $asistencia->setRelation('empleado', $empleado);
                $servicio->calcular($asistencia);
                $asistencia->save();
            }
        }

        // -------------------- Justificaciones / solicitudes --------------------
        foreach (array_slice($empleados, 0, 6) as $i => $empleado) {
            Justificacion::create([
                'empleado_id' => $empleado->id,
                'fecha' => Carbon::now()->subDays(3 + $i)->toDateString(),
                'motivo' => ['Cita médica', 'Trámite personal', 'Capacitación externa', 'Problema familiar'][$i % 4],
                'horario_afectado' => 'Jornada completa',
                'estado' => ['Pendiente', 'Aprobada', 'Pendiente', 'Rechazada', 'Aprobada', 'Pendiente'][$i],
                'comunicante' => $empleado->nombre_completo,
                'explicacion' => 'Documento presentado en físico al área de Recursos Humanos.',
            ]);
            SolicitudVacaciones::create([
                'empleado_id' => $empleado->id,
                'tipo' => 'Vacaciones',
                'desde' => Carbon::now()->addDays(10 + $i)->toDateString(),
                'hasta' => Carbon::now()->addDays(16 + $i)->toDateString(),
                'dias' => 7,
                'estado' => $i % 2 ? 'Pendiente' : 'Aprobada',
                'motivo' => 'Descanso vacacional pendiente del período.',
            ]);
            SolicitudPermiso::create([
                'empleado_id' => $empleado->id,
                'fecha' => Carbon::now()->addDays(2 + $i)->toDateString(),
                'hora_desde' => '09:00:00', 'hora_hasta' => '11:00:00',
                'motivo' => ['Trámite bancario', 'Consulta médica', 'Diligencias personales'][$i % 3],
                'estado' => $i % 2 ? 'Pendiente' : 'Aprobada',
            ]);
        }

        // --------------------------- Conceptos y planilla ---------------------
        foreach ([
            ['REM', 'Remuneración básica', 'Ingreso', 0, 0],
            ['HEX', 'Horas extras', 'Ingreso', 0, 0],
            ['BON', 'Bono de asistencia perfecta', 'Ingreso', 50, 0],
            ['TAR', 'Descuento por tardanza', 'Descuento', 0, 0],
            ['FA', 'Descuento por falta', 'Descuento', 0, 0],
            ['ONP', 'Aporte ONP (13 %)', 'Aporte', 0, 13],
            ['AFP', 'Aporte AFP (10 %)', 'Aporte', 0, 10],
            ['R5', 'Renta de quinta categoría', 'Impuesto', 0, 0],
        ] as [$codigo, $nombre, $tipo, $monto, $porcentaje]) {
            ConceptoSalarial::create([
                'codigo' => $codigo, 'nombre' => $nombre, 'tipo' => $tipo,
                'monto' => $monto, 'porcentaje' => $porcentaje,
                'descripcion' => $nombre.' aplicado en la planilla mensual.',
            ]);
        }

        $planillaServicio = app(PlanillaService::class);
        foreach ([1, 0] as $meses) {
            $mes = Carbon::now()->startOfMonth()->subMonths($meses);
            $periodo = PeriodoPlanilla::create([
                'periodo' => ucfirst($mes->copy()->locale('es')->translatedFormat('F Y')),
                'anio' => $mes->year, 'mes' => $mes->month,
                'estado' => $meses ? 'Pagado' : 'Revisión',
                'fecha_cierre' => $mes->copy()->endOfMonth()->toDateString(),
                'fecha_pago' => $mes->copy()->endOfMonth()->addDays(2)->toDateString(),
            ]);
            $planillaServicio->calcularPeriodo($periodo);
        }

        // ------------------------- Estación IoT demo --------------------------
        \App\Models\Sistema\IotEstacion::create([
            'nombre' => 'estacion-1', 'ubicacion' => 'Oficina RR.HH. · Mesa 1',
            'sucursal_id' => $sucursal->id, 'empleado_id' => $empleados[0]->id,
            'tipo' => 'camara', 'activo' => true, 'ultimo_ping' => now(),
        ]);
        \App\Models\Sistema\IotEstacion::create([
            'nombre' => 'estacion-2', 'ubicacion' => 'Ingreso principal',
            'sucursal_id' => $sucursal->id, 'tipo' => 'qr', 'activo' => true,
        ]);

        $this->command?->info('Datos de demostración cargados: '.Empleado::count().' empleados, '
            .Asistencia::count().' marcaciones, '.PeriodoPlanilla::count().' períodos de planilla.');
// ===== Salidas temporales de demostración (v8) =====
        \Illuminate\Support\Facades\DB::table('salidas_temporales')->delete();
        $idsDemo = \App\Models\Empleados\Empleado::orderBy('id')->limit(3)->pluck('id');
        foreach ($idsDemo as $eid) {
            \Illuminate\Support\Facades\DB::table('salidas_temporales')->insert([
                'empleado_id' => $eid, 'motivo' => 'Necesidad personal.',
                'observacion' => 'Salida breve de demostración.',
                'hora_salida' => now()->setTime(10, 35), 'hora_retorno' => now()->setTime(10, 37),
                'estado' => 'Completada', 'tolerancia_min' => 5,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        if ($idsDemo->isNotEmpty()) {
            \Illuminate\Support\Facades\DB::table('salidas_temporales')->insert([
                'empleado_id' => $idsDemo->first(), 'motivo' => 'Atención médica.',
                'observacion' => 'Control médico programado (demostración).',
                'hora_salida' => now()->subMinutes(30), 'hora_retorno' => null,
                'estado' => 'Monitoreo pausado', 'tolerancia_min' => 5,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $this->call(ConfiguracionSeeder::class);
    }
}
