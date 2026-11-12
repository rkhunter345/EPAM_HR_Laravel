<?php

namespace App\Http\Controllers\Empleados;

use App\Http\Controllers\Controller;

use App\Models\Configuracion\CalendarioTrabajo;
use App\Models\Organizacion\Compania;
use App\Models\Organizacion\Departamento;
use App\Models\Organizacion\Designacion;
use App\Models\Empleados\Empleado;
use App\Models\Organizacion\GradoEmpleado;
use App\Models\Organizacion\GrupoEmpleados;
use App\Models\Organizacion\Sucursal;
use App\Models\Configuracion\TipoContrato;
use App\Models\Configuracion\TipoEmpleo;
use Illuminate\Http\Request;

class EmpleadoController extends Controller
{
    /** Catálogos para los selects del formulario. */
    private function catalogos(): array
    {
        return [
            'companias' => Compania::orderBy('nombre')->pluck('nombre', 'id'),
            'sucursales' => Sucursal::orderBy('nombre')->pluck('nombre', 'id'),
            'departamentos' => Departamento::orderBy('nombre')->pluck('nombre', 'id'),
            'designaciones' => Designacion::orderBy('nombre')->pluck('nombre', 'id'),
            'grados' => GradoEmpleado::orderBy('nombre')->pluck('nombre', 'id'),
            'grupos' => GrupoEmpleados::orderBy('nombre')->pluck('nombre', 'id'),
            'tiposEmpleo' => TipoEmpleo::orderBy('nombre')->pluck('nombre', 'id'),
            'tiposContrato' => TipoContrato::orderBy('nombre')->pluck('nombre', 'id'),
            'calendarios' => CalendarioTrabajo::orderBy('nombre')->pluck('nombre', 'id'),
        ];
    }

    public function index(Request $request)
    {
        $q = Empleado::query()->with(['departamento', 'designacion', 'sucursal']);

        if ($texto = trim((string) $request->query('q'))) {
            $q->where(fn ($s) => $s->where('nombre_completo', 'like', "%{$texto}%")->orWhere('codigo', 'like', "%{$texto}%"));
        }
        if ($codigo = trim((string) $request->query('codigo'))) {
            $q->where('codigo', 'like', "%{$codigo}%");
        }
        foreach (['departamento_id', 'designacion_id', 'estado'] as $filtro) {
            if ($valor = $request->query($filtro)) {
                $q->where($filtro, $valor);
            }
        }

        $ordenSql = match ((string) $request->query('orden', 'nombre')) {
            'reciente' => ['id', 'desc'],
            'salario' => ['salario_mensual', 'desc'],
            'codigo' => ['codigo', 'asc'],
            default => ['nombre_completo', 'asc'],
        };
        $empleados = $q->orderBy($ordenSql[0], $ordenSql[1])->paginate(20)->withQueryString();

        return view('empleados.index', [
            'empleados' => $empleados,
            'departamentos' => Departamento::orderBy('nombre')->pluck('nombre', 'id'),
            'designaciones' => Designacion::orderBy('nombre')->pluck('nombre', 'id'),
            'total' => Empleado::count(),
            'activos' => Empleado::where('estado', 'Activo')->count(),
            'proceso' => Empleado::where('estado', 'En proceso')->count(),
            'inactivos' => Empleado::where('estado', 'Inactivo')->count(),
            'nuevos' => Empleado::whereYear('fecha_ingreso', now()->year)->count(),
            'salidas' => Empleado::where('estado', 'Inactivo')->whereYear('fecha_fin_contrato', now()->year)->count(),
        ]);
    }

    public function create()
    {
        return view('empleados.create', $this->catalogos() + ['empleado' => new Empleado(['estado' => 'Activo', 'moneda_salario' => 'PEN', 'tipo_salario' => 'Mensual'])]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        $datos['codigo'] = Empleado::siguienteCodigo();
        $datos['nombre_completo'] = trim(($datos['primer_nombre'] ?? '').' '.($datos['segundo_nombre'] ?? '').' '.($datos['apellido'] ?? ''));

        if (blank($datos['fecha_ingreso'] ?? null)) {
            $datos['fecha_ingreso'] = now()->toDateString();
        }

        $empleado = Empleado::create($datos);

        return redirect()->route('empleados.show', $empleado)
            ->with('ok', "Empleado {$empleado->nombre_completo} registrado con código {$empleado->codigo}.");
    }

    public function show(Empleado $empleado)
    {
        $empleado->load(['compania', 'sucursal', 'departamento', 'designacion', 'grado', 'grupo', 'tipoEmpleo', 'tipoContrato', 'calendario']);

        $asistencias = $empleado->asistencias()->orderByDesc('fecha')->limit(15)->get();
        $resumen = [
            'asistio' => $empleado->asistencias()->where('estado', 'Asistió')->count(),
            'tardanza' => $empleado->asistencias()->where('estado', 'Tardanza')->count(),
            'ausente' => $empleado->asistencias()->where('estado', 'Ausente')->count(),
        ];
        $vacaciones = $empleado->vacaciones()->orderByDesc('desde')->limit(5)->get();
        $planillas = $empleado->detallesPlanilla()->with('periodo')->orderByDesc('id')->limit(6)->get();

        return view('empleados.show', compact('empleado', 'asistencias', 'resumen', 'vacaciones', 'planillas'));
    }

    public function edit(Empleado $empleado)
    {
        return view('empleados.edit', $this->catalogos() + compact('empleado'));
    }

    public function update(Request $request, Empleado $empleado)
    {
        $datos = $this->validar($request);
        $datos['nombre_completo'] = trim(($datos['primer_nombre'] ?? '').' '.($datos['segundo_nombre'] ?? '').' '.($datos['apellido'] ?? ''));
        $empleado->update($datos);

        return redirect()->route('empleados.show', $empleado)->with('ok', 'Empleado actualizado correctamente.');
    }

    public function destroy(Empleado $empleado)
    {
        $empleado->delete();

        return redirect()->route('empleados.index')->with('ok', 'Empleado eliminado del padrón.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'primer_nombre' => 'required|string|max:80',
            'segundo_nombre' => 'nullable|string|max:80',
            'apellido' => 'required|string|max:80',
            'genero' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'estado' => 'required|string|max:20',
            'compania_id' => 'nullable|exists:companias,id',
            'sucursal_id' => 'nullable|exists:sucursales,id',
            'departamento_id' => 'nullable|exists:departamentos,id',
            'designacion_id' => 'nullable|exists:designaciones,id',
            'grado_empleado_id' => 'nullable|exists:grados_empleado,id',
            'grupo_id' => 'nullable|exists:grupos_empleados,id',
            'tipo_empleo_id' => 'nullable|exists:tipos_empleo,id',
            'tipo_contrato_id' => 'nullable|exists:tipos_contrato,id',
            'calendario_id' => 'nullable|exists:calendarios_trabajo,id',
            'salario_mensual' => 'nullable|numeric|min:0',
            'moneda_salario' => 'nullable|string|max:10',
            'tipo_salario' => 'nullable|string|max:30',
            'frecuencia_pago' => 'nullable|string|max:30',
            'metodo_pago' => 'nullable|string|max:40',
            'asignacion_familiar' => 'nullable|boolean',
            'afp' => 'nullable|string|in:ONP,Habitat,Integra,Prima,Profuturo',
            'cuenta_bancaria' => 'nullable|string|max:40',
            'jornada_laboral' => 'nullable|string|max:40',
            'horas_semanales' => 'nullable|numeric|min:0|max:60',
            'proyecto_cliente' => 'nullable|string|max:120',
            'fecha_ingreso' => 'nullable|date',
            'fecha_confirmacion' => 'nullable|date',
            'fecha_fin_contrato' => 'nullable|date',
            'tipo_documento' => 'nullable|string|max:30',
            'numero_documento' => 'nullable|string|max:20',
            'estado_civil' => 'nullable|string|max:30',
            'telefono' => 'nullable|string|max:30',
            'correo_personal' => 'nullable|email|max:120',
            'direccion' => 'nullable|string|max:200',
            'distrito' => 'nullable|string|max:80',
            'contacto_emergencia' => 'nullable|string|max:120',
            'telefono_emergencia' => 'nullable|string|max:30',
            'observaciones' => 'nullable|string|max:600',
        ], [
            'primer_nombre.required' => 'El primer nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
        ]);
    }
}
