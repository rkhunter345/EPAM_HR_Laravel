<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Asistencias\Asistencia;
use App\Models\Configuracion\CalendarioTrabajo;
use App\Models\Empleados\Empleado;
use App\Models\Organizacion\Compania;
use App\Models\Organizacion\Departamento;
use App\Models\Organizacion\Designacion;
use App\Models\Organizacion\GrupoEmpleados;
use App\Models\Organizacion\Sucursal;
use App\Models\Configuracion\TipoContrato;
use App\Models\Configuracion\TipoEmpleo;
use App\Models\Nomina\ConceptoSalarial;
use App\Models\Sistema\Auditoria;
use App\Models\Sistema\Integracion;
use App\Models\Sistema\IotEstacion;
use App\Models\Sistema\Parametro;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Rol ADMINISTRADOR — configuración técnica y seguridad de la plataforma.
 * Por separación de roles NO recibe acceso automático a RR. HH. ni nómina.
 */
class SistemaController extends Controller
{
    public const MODULOS = [
        'Panel técnico',
        'Usuarios y cuentas',
        'Roles y permisos',
        'Parámetros del sistema',
        'Asistencia y tolerancias',
        'Catálogos',
        'Integraciones',
        'Auditoría',
        'Perfil',
    ];

    public function panel()
    {
        return view('sistema.panel', [
            'cuentasActivas' => User::where('activo', true)->count(),
            'cuentasInactivas' => User::where('activo', false)->count(),
            'roles' => count(User::ROLES),
            'integraconCaidas' => Integracion::where('estado', '!=', 'Conectado')->count(),
            'integraciones' => Integracion::orderBy('id')->get(),
            'eventos' => Auditoria::latest('id')->limit(6)->get(),
            'pendientes' => Parametro::whereNull('valor')->orWhere('valor', '')->count(),
            'parametros' => Parametro::orderBy('grupo')->get(),
            'totalUsuarios' => User::count(),
            'operativos' => Integracion::where('activo', true)->count(),
        ]);
    }

    public function usuarios(Request $request)
    {
        $q = User::with('empleado');
        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $q->where(fn ($w) => $w->where('name', 'like', "%$b%")->orWhere('usuario', 'like', "%$b%"));
        }

        return view('sistema.usuarios', [
            'usuarios' => $q->orderBy('name')->get(),
            'empleados' => Empleado::orderBy('nombre_completo')->get(),
            'activas' => User::where('activo', true)->count(),
            'inactivas' => User::where('activo', false)->count(),
            'supervisores' => User::where('rol', 'SUPERVISOR')->count(),
            'reset' => 0,
        ]);
    }

    public function guardarUsuario(Request $request)
    {
        $datos = $request->validate([
            'name' => 'required|string|max:120',
            'usuario' => 'required|string|max:40|unique:users,usuario',
            'email' => 'nullable|email|max:120',
            'rol' => 'required|in:ADMIN,RRHH,SUPERVISOR,EMPLEADO',
            'empleado_id' => 'nullable|exists:empleados,id',
        ], ['usuario.unique' => 'Ese usuario ya existe.']);

        // FASE 2.G: sin contraseña temporal fija codificada; se genera una aleatoria de un solo uso.
        $temporal = 'Epam-'.bin2hex(random_bytes(5));
        $nuevo = User::create($datos + ['password' => Hash::make($temporal), 'activo' => true]);

        Auditoria::create([
            'usuario_id' => auth()->id(), 'usuario' => auth()->user()->name, 'accion' => 'Creación de cuenta',
            'modulo' => 'Usuarios', 'detalle' => $nuevo->usuario.' · rol '.$nuevo->rol,
            'ip' => $request->ip(), 'created_at' => now(),
        ]);

        return back()->with('ok', 'Cuenta creada. Entregue al colaborador esta contraseña temporal por un canal seguro y solicítele cambiarla al primer ingreso: '.$temporal);
    }

    public function estadoUsuario(Request $request, User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puede desactivar su propia cuenta.');
        }
        // FASE 2.G: impedir desactivar la última cuenta ADMIN activa.
        if ($usuario->rol === 'ADMIN' && $usuario->activo
            && User::where('rol', 'ADMIN')->where('activo', true)->count() <= 1) {
            return back()->with('error', 'No se puede desactivar la última cuenta ADMIN activa.');
        }
        $usuario->activo = ! $usuario->activo;
        $usuario->save();

        Auditoria::create([
            'usuario_id' => auth()->id(), 'usuario' => auth()->user()->name, 'accion' => 'Cambio de estado de cuenta',
            'modulo' => 'Usuarios', 'detalle' => $usuario->usuario.' → '.($usuario->activo ? 'Activa' : 'Desactivada'),
            'ip' => $request->ip(), 'created_at' => now(),
        ]);

        return back()->with('ok', 'Estado de la cuenta actualizado.');
    }

    /**
     * FASE 2.A: política de permisos FIJA por rol (mínimo privilegio).
     * La matriz se muestra como política informativa NO editable: la
     * autorización real la aplica el middleware 'rol' en el servidor y ya no
     * depende de la tabla permisos_rol. Los límites máximos por rol no son
     * configurables desde la interfaz.
     */
    public function roles()
    {
        $alcance = [
            'ADMIN' => 'Módulos técnicos: panel, cuentas, roles, parámetros, catálogos, integraciones, auditoría. Sin expedientes, asistencia detallada, información médica ni remuneraciones.',
            'RRHH' => 'Personal, asistencia, justificaciones, emergencias, vacaciones, nómina, boletas y reportes laborales. Sin cuentas ni configuración técnica.',
            'SUPERVISOR' => 'Su espacio personal y la supervisión del equipo/departamento asignado. Sin nómina ni salarios y sin revisar sus propios casos.',
            'EMPLEADO' => 'Su propio expediente, marcaciones, asistencia, salidas, alertas, solicitudes y sus boletas.',
        ];

        $matriz = collect(self::MODULOS)->map(fn ($m) => [
            'modulo' => $m,
            'ADMIN' => true,
            'RRHH' => ! in_array($m, ['Usuarios y cuentas', 'Panel técnico', 'Roles y permisos', 'Integraciones', 'Auditoría'], true),
            'SUPERVISOR' => $m === 'Panel técnico',
            'EMPLEADO' => $m === 'Perfil',
        ]);

        return view('sistema.roles', [
            'matriz' => $matriz,
            'alcance' => $alcance,
            'roles' => User::ROLES,
            'usuariosPorRol' => User::selectRaw('rol, count(*) as total')->groupBy('rol')->pluck('total', 'rol'),
        ]);
    }

    public function parametros()
    {
        return view('sistema.parametros', [
            'parametros' => Parametro::orderBy('grupo')->orderBy('id')->get(),
            'grupos' => Parametro::select('grupo')->distinct()->pluck('grupo'),
            'seguridad' => Parametro::where('grupo', 'seguridad')->get(),
            'notificaciones' => Parametro::where('grupo', 'notificacion')->get(),
        ]);
    }

    public function guardarParametros(Request $request)
    {
        $cambios = [];
        foreach ($request->except('_token') as $clave => $valor) {
            $p = Parametro::where('clave', $clave)->first();
            if (! $p) {
                continue;
            }
            $anterior = $p->valor;
            $p->valor = is_array($valor) ? implode(',', $valor) : (string) $valor;
            $p->actualizado_por = auth()->id();
            $p->save();
            if ($anterior !== $p->valor) {
                $cambios[] = $clave.': '.$anterior.' → '.$p->valor;
            }
        }

        if ($cambios) {
            Auditoria::create([
                'usuario_id' => auth()->id(), 'usuario' => auth()->user()->name, 'accion' => 'Cambio de parámetros',
                'modulo' => 'Parámetros', 'detalle' => implode(' | ', $cambios),
                'ip' => $request->ip(), 'created_at' => now(),
            ]);
        }

        return back()->with('ok', 'Parámetros actualizados. Se conservó el valor anterior en la auditoría.');
    }

    public function tolerancias()
    {
        return view('sistema.tolerancias', [
            'parametros' => Parametro::whereIn('grupo', ['asistencia', 'tolerancia'])->get(),
            'calendarios' => CalendarioTrabajo::orderBy('nombre')->get(),
            'tolerancia' => (int) Parametro::valor('tolerancia_ausencia', 5),
            'monitoreo' => (bool) Parametro::valor('monitoreo_iot', 1),
            'estaciones' => IotEstacion::with('empleado')->get(),
            'marcacionesHoy' => Asistencia::whereDate('fecha', now()->toDateString())->count(),
        ]);
    }

    public function catalogos()
    {
        return view('sistema.catalogos', [
            'companias' => Compania::orderBy('nombre')->get(),
            'sucursales' => Sucursal::orderBy('nombre')->get(),
            'departamentos' => Departamento::withCount('empleados')->orderBy('nombre')->get(),
            'designaciones' => Designacion::orderBy('nombre')->get(),
            'grupos' => GrupoEmpleados::orderBy('nombre')->get(),
            'tiposEmpleo' => TipoEmpleo::orderBy('nombre')->get(),
            'tiposContrato' => TipoContrato::orderBy('nombre')->get(),
            'calendarios' => CalendarioTrabajo::orderBy('nombre')->get(),
            'conceptos' => ConceptoSalarial::orderBy('codigo')->get(),
            'motivosSalida' => \App\Models\Asistencias\SalidaTemporal::MOTIVOS,
        ]);
    }

    public function integraciones()
    {
        return view('sistema.integraciones', [
            'integraciones' => Integracion::orderBy('id')->get(),
            'conectadas' => Integracion::where('estado', 'Conectado')->count(),
            'caidas' => Integracion::where('estado', '!=', 'Conectado')->count(),
            'activas' => Integracion::where('activo', true)->count(),
        ]);
    }

    /**
     * FASE 4: etiquetado honesto. NO se hace una comprobación real de
     * conectividad (eso corresponde a otra etapa, desde la red del propietario).
     * La acción queda registrada como simulación de interfaz, sin fingir estado.
     */
    public function probarIntegracion(Request $request, Integracion $integracion)
    {
        $integracion->ultima_sincronizacion = now();
        $integracion->estado = 'Sin verificar';
        $integracion->save();

        Auditoria::create([
            'usuario_id' => auth()->id(), 'usuario' => auth()->user()->name, 'accion' => 'Prueba de integración (simulación de interfaz)',
            'modulo' => 'Integraciones', 'detalle' => $integracion->nombre.' · marcado sin verificación real; la conectividad se probará en despliegue local',
            'ip' => $request->ip(), 'created_at' => now(),
        ]);

        return back()->with('ok', 'Se registró la intención de prueba de '.$integracion->nombre.'. La verificación real de conectividad se realizará en otra etapa; el estado «Conectado» no se simula.');
    }

    public function auditoria(Request $request)
    {
        $q = Auditoria::query();
        if ($request->filled('modulo')) {
            $q->where('modulo', $request->modulo);
        }
        if ($request->filled('usuario')) {
            $q->where('usuario', 'like', '%'.$request->usuario.'%');
        }
        if ($request->filled('desde')) {
            $q->whereDate('created_at', '>=', $request->desde);
        }

        return view('sistema.auditoria', [
            'eventos' => $q->latest('id')->paginate(12)->withQueryString(),
            'modulos' => Auditoria::select('modulo')->distinct()->pluck('modulo'),
            'total' => Auditoria::count(),
            'usuarios' => Auditoria::select('usuario')->distinct()->count(),
            'cambios' => Auditoria::whereIn('accion', ['Cambio de permisos por rol', 'Cambio de parámetros', 'Cambio de estado de cuenta'])->count(),
            'hoy' => Auditoria::whereDate('created_at', now()->toDateString())->count(),
        ]);
    }
}
