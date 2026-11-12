<?php

namespace Tests\Feature;

use App\Models\Asistencias\Justificacion;
use App\Models\Asistencias\SolicitudVacaciones;
use App\Models\Empleados\Empleado;
use App\Models\Nomina\DetallePlanilla;
use App\Models\Nomina\PeriodoPlanilla;
use App\Models\Organizacion\Departamento;
use App\Models\Sistema\IotEvento;
use App\Models\Sistema\IotEstacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * FASE 6 — Pruebas de autorización y aislamiento por rol.
 * Suite local (SQLite en memoria). No requiere conexión externa.
 */
class AutorizacionTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $rol, ?Empleado $empleado = null, bool $activo = true): User
    {
        return User::create([
            'name' => 'Usuario '.$rol.uniqid(),
            'usuario' => 'u'.uniqid(),
            'password' => Hash::make('ClaveSegura2026'),
            'rol' => $rol,
            'empleado_id' => $empleado?->id,
            'activo' => $activo,
        ]);
    }

    private function empleado(?int $departamentoId = null): Empleado
    {
        return Empleado::create([
            'codigo' => 'HR-EMP-'.uniqid(),
            'nombre_completo' => 'Colaborador '.uniqid(),
            'estado' => 'Activo',
            'departamento_id' => $departamentoId,
        ]);
    }

    // --- Autenticación -----------------------------------------------------

    public function test_usuario_no_autenticado_es_redirigido_al_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/mi-espacio')->assertRedirect(route('login'));
    }

    public function test_cuenta_inactiva_no_puede_iniciar_sesion(): void
    {
        $this->usuario('EMPLEADO', null, false);

        $this->from(route('login'))->post(route('login.post'), [
            'usuario' => User::first()->usuario,
            'password' => 'ClaveSegura2026',
        ])->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_login_tiene_limite_de_intentos(): void
    {
        $u = $this->usuario('EMPLEADO');

        for ($i = 0; $i < 6; $i++) {
            $this->post(route('login.post'), ['usuario' => $u->usuario, 'password' => 'incorrecta']);
        }

        $this->post(route('login.post'), ['usuario' => $u->usuario, 'password' => 'ClaveSegura2026'])
            ->assertRedirect(route('login'));
        $this->assertGuest();
    }

    // --- Límites por rol ---------------------------------------------------

    public function test_empleado_no_accede_al_panel_de_rrhh(): void
    {
        $u = $this->usuario('EMPLEADO');
        $this->actingAs($u)->get(route('rrhh.panel'))->assertForbidden();
    }

    public function test_empleado_no_accede_al_panel_de_sistema(): void
    {
        $u = $this->usuario('EMPLEADO');
        $this->actingAs($u)->get(route('sistema.usuarios'))->assertForbidden();
    }

    public function test_supervisor_no_accede_a_nomina(): void
    {
        $u = $this->usuario('SUPERVISOR');
        $this->actingAs($u)->get(route('rrhh.nomina'))->assertForbidden();
        $this->actingAs($u)->get(route('rrhh.boletas'))->assertForbidden();
    }

    // --- Aislamiento de expedientes ----------------------------------------

    public function test_empleado_recibe_403_en_boleta_ajena(): void
    {
        $depto = Departamento::create(['nombre' => 'D '.uniqid(), 'activo' => true]);
        $a = $this->empleado($depto->id);
        $b = $this->empleado($depto->id);

        $periodo = PeriodoPlanilla::create([
            'periodo' => 'Noviembre 2026', 'anio' => 2026, 'mes' => 11, 'estado' => 'Pagado',
        ]);
        $detalleAjeno = DetallePlanilla::create([
            'periodo_planilla_id' => $periodo->id, 'empleado_id' => $b->id,
            'basico' => 1000, 'total_ingresos' => 1000, 'total_descuentos' => 100, 'neto' => 900,
        ]);

        $u = $this->usuario('EMPLEADO', $a);
        $this->actingAs($u)->get(route('minomina.boleta', $detalleAjeno))->assertForbidden();
    }

    public function test_empleado_descarga_su_propia_boleta_publicada(): void
    {
        $a = $this->empleado();
        $periodo = PeriodoPlanilla::create([
            'periodo' => 'Noviembre 2026', 'anio' => 2026, 'mes' => 11, 'estado' => 'Pagado',
        ]);
        $detalle = DetallePlanilla::create([
            'periodo_planilla_id' => $periodo->id, 'empleado_id' => $a->id,
            'basico' => 1000, 'total_ingresos' => 1000, 'total_descuentos' => 100, 'neto' => 900, 'afp' => 'ONP',
        ]);

        $u = $this->usuario('EMPLEADO', $a);
        $this->actingAs($u)->get(route('minomina.boleta', $detalle))->assertOk();
    }

    public function test_boleta_en_borrador_no_se_publica(): void
    {
        $a = $this->empleado();
        $periodo = PeriodoPlanilla::create([
            'periodo' => 'Diciembre 2026', 'anio' => 2026, 'mes' => 12, 'estado' => 'Borrador',
        ]);
        $detalle = DetallePlanilla::create([
            'periodo_planilla_id' => $periodo->id, 'empleado_id' => $a->id,
            'basico' => 1000, 'total_ingresos' => 1000, 'total_descuentos' => 0, 'neto' => 1000, 'afp' => 'ONP',
        ]);

        $u = $this->usuario('EMPLEADO', $a);
        $this->actingAs($u)->get(route('minomina.boleta', $detalle))->assertForbidden();
    }

    public function test_empleado_sin_vinculo_no_descarga_boleta(): void
    {
        $u = $this->usuario('EMPLEADO', null);
        $a = $this->empleado();
        $periodo = PeriodoPlanilla::create([
            'periodo' => 'Noviembre 2026', 'anio' => 2026, 'mes' => 11, 'estado' => 'Pagado',
        ]);
        $detalle = DetallePlanilla::create([
            'periodo_planilla_id' => $periodo->id, 'empleado_id' => $a->id,
            'basico' => 1000, 'total_ingresos' => 1000, 'total_descuentos' => 0, 'neto' => 1000, 'afp' => 'ONP',
        ]);

        $this->actingAs($u)->get(route('minomina.boleta', $detalle))->assertForbidden();
    }

    // --- Adjuntos privados (FASE 5) ----------------------------------------

    public function test_empleado_no_descarga_adjunto_ajeno(): void
    {
        $a = $this->empleado();
        $b = $this->empleado();
        $j = Justificacion::create([
            'empleado_id' => $b->id, 'fecha' => now()->toDateString(),
            'motivo' => 'Atención médica.', 'estado' => 'Pendiente', 'documento' => 'justificaciones/x.pdf',
        ]);

        $u = $this->usuario('EMPLEADO', $a);
        $this->actingAs($u)->get(route('espacio.justificaciones.adjunto', $j))->assertForbidden();
    }

    // --- Supervisor: fallo seguro y ámbito (FASE 2.B / 2.C) ----------------

    public function test_supervisor_sin_equipo_no_ve_a_toda_la_plantilla(): void
    {
        // Otros empleados activos existen en la base, pero sin departamento
        // vinculado al supervisor el panel no debe listar a nadie.
        $this->empleado();
        $this->empleado();
        $sup = $this->empleado(null);
        $u = $this->usuario('SUPERVISOR', $sup);

        $this->actingAs($u)->get(route('equipo.panel'))
            ->assertOk()
            ->assertSee('0');
    }

    public function test_supervisor_no_revisa_casos_de_otro_departamento(): void
    {
        $deptoAjeno = Departamento::create(['nombre' => 'Ajeno '.uniqid(), 'activo' => true]);
        $otro = $this->empleado($deptoAjeno->id);
        $j = Justificacion::create([
            'empleado_id' => $otro->id, 'fecha' => now()->toDateString(),
            'motivo' => 'Falla técnica.', 'estado' => 'Pendiente',
        ]);

        $depto = Departamento::create(['nombre' => 'Propio '.uniqid(), 'activo' => true]);
        $sup = $this->empleado($depto->id);
        $u = $this->usuario('SUPERVISOR', $sup);

        $this->actingAs($u)
            ->from(route('equipo.solicitudes'))
            ->post(route('equipo.revisar', ['justificacion', $j->id]), ['decision' => 'Aprobada'])
            ->assertForbidden();

        $this->assertEquals('Pendiente', $j->fresh()->estado);
    }

    public function test_supervisor_no_revisa_sus_propios_casos(): void
    {
        $depto = Departamento::create(['nombre' => 'D '.uniqid(), 'activo' => true]);
        $sup = $this->empleado($depto->id);
        $j = Justificacion::create([
            'empleado_id' => $sup->id, 'fecha' => now()->toDateString(),
            'motivo' => 'Falla técnica.', 'estado' => 'Pendiente',
        ]);
        $u = $this->usuario('SUPERVISOR', $sup);

        $this->actingAs($u)
            ->from(route('equipo.solicitudes'))
            ->post(route('equipo.revisar', ['justificacion', $j->id]), ['decision' => 'Aprobada'])
            ->assertRedirect();

        $this->assertEquals('Pendiente', $j->fresh()->estado);
    }

    public function test_supervisor_revisa_casos_de_su_equipo(): void
    {
        $depto = Departamento::create(['nombre' => 'D '.uniqid(), 'activo' => true]);
        $miembro = $this->empleado($depto->id);
        $sup = $this->empleado($depto->id);
        $v = SolicitudVacaciones::create([
            'empleado_id' => $miembro->id, 'tipo' => 'Vacaciones', 'dias' => 5,
            'desde' => now()->addWeek()->toDateString(), 'hasta' => now()->addWeeks(2)->toDateString(),
            'estado' => 'Pendiente', 'motivo' => 'Descanso',
        ]);
        $u = $this->usuario('SUPERVISOR', $sup);

        $this->actingAs($u)
            ->from(route('equipo.solicitudes'))
            ->post(route('equipo.revisar', ['vacaciones', $v->id]), ['decision' => 'Aprobada'])
            ->assertRedirect();

        $this->assertEquals('Aprobada', $v->fresh()->estado);
        $this->assertEquals($u->id, $v->fresh()->aprobado_por);
    }

    // --- Salidas temporales (FASE 2.D) -------------------------------------

    public function test_empleado_no_cierra_salida_ajena(): void
    {
        $a = $this->empleado();
        $b = $this->empleado();
        $salida = \App\Models\Asistencias\SalidaTemporal::create([
            'empleado_id' => $b->id, 'motivo' => 'Necesidad personal.',
            'hora_salida' => now(), 'estado' => \App\Models\Asistencias\SalidaTemporal::ESTADO_ACTIVA,
        ]);

        $u = $this->usuario('EMPLEADO', $a);
        $this->actingAs($u)
            ->from(route('salidas.index'))
            ->post(route('salidas.regresar', $salida))
            ->assertForbidden();

        $this->assertNull($salida->fresh()->hora_retorno);
    }

    // --- IoT (FASE 2.F) ----------------------------------------------------

    public function test_evento_iot_sin_token_no_se_crea(): void
    {
        $this->post(route('api.iot.evento'), ['estacion' => 'estacion-1', 'tipo' => 'ausencia'])
            ->assertStatus(401);

        $this->assertSame(0, IotEvento::count());
    }

    public function test_evento_iot_con_token_invalido_no_se_crea(): void
    {
        $this->post(route('api.iot.evento'), ['estacion' => 'estacion-1', 'tipo' => 'ausencia'],
            ['X-IOT-TOKEN' => 'token-falso'])
            ->assertStatus(401);

        $this->assertSame(0, IotEvento::count());
    }

    public function test_evento_iot_valido_no_devuelve_nombres_personales(): void
    {
        $estacion = IotEstacion::create(['nombre' => 'estacion-test', 'tipo' => 'camara', 'activo' => true]);

        $respuesta = $this->post(route('api.iot.evento'),
            ['estacion' => 'estacion-test', 'tipo' => 'ausencia'],
            ['X-IOT-TOKEN' => 'token-de-pruebas-local']);

        $respuesta->assertCreated()->assertJsonMissing(['nombre_completo']);
        $this->assertSame(1, IotEvento::count());
    }

    // --- ADMIN (FASE 2.G) --------------------------------------------------

    public function test_no_se_desactiva_la_ultima_cuenta_admin_activa(): void
    {
        $admin = $this->usuario('ADMIN');

        $this->actingAs($admin)
            ->from(route('sistema.usuarios'))
            ->post(route('sistema.usuarios.estado', $admin))
            ->assertRedirect();

        $this->assertTrue((bool) $admin->fresh()->activo);
    }

    public function test_contraseña_temporal_no_es_fija(): void
    {
        $admin = $this->usuario('ADMIN');

        $this->actingAs($admin)->post(route('sistema.usuarios.guardar'), [
            'name' => 'Nuevo Colaborador', 'usuario' => 'nuevo'.uniqid(),
            'rol' => 'EMPLEADO',
        ]);

        $this->assertDatabaseMissing('users', ['password' => Hash::make('epam2026')]);
        $this->assertFalse(Hash::check('epam2026', User::where('usuario', 'like', 'nuevo%')->first()->password));
    }
}
