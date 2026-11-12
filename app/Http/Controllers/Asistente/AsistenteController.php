<?php

namespace App\Http\Controllers\Asistente;

use App\Http\Controllers\Controller;
use App\Models\Asistencias\Asistencia;
use App\Models\Empleados\Empleado;
use App\Models\Nomina\PeriodoPlanilla;
use App\Services\AsistenciaService;
use App\Services\OllamaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Asistente inteligente de RR.HH.
 * Motor principal: Ollama local (OLLAMA_URL / OLLAMA_MODELO). Si Ollama no
 * está disponible responde con el motor local de reglas, avisando el motor
 * usado. Sólo consulta: no aprueba ni modifica registros, y el contexto que
 * recibe el modelo contiene únicamente datos autorizados por el rol.
 */
class AsistenteController extends Controller
{
    public function index()
    {
        $ollama = app(OllamaService::class);

        return view('asistente.index', [
            'pregunta' => null,
            'respuesta' => null,
            'ollamaOk' => $ollama->disponible(),
            'modelo' => $ollama->modelo(),
        ]);
    }

    public function preguntar(Request $request, AsistenciaService $asistencias)
    {
        $datos = $request->validate(['pregunta' => 'required|string|max:300']);
        $respuesta = $this->procesar($datos['pregunta'], $request->user(), $asistencias);

        return view('asistente.index', ['pregunta' => $datos['pregunta']] + $respuesta
            + ['ollamaOk' => app(OllamaService::class)->disponible(),
               'modelo' => app(OllamaService::class)->modelo()]);
    }

    /** API JSON para el panel flotante del asistente (botón robot). */
    public function api(Request $request, AsistenciaService $asistencias): JsonResponse
    {
        $datos = $request->validate(['pregunta' => 'required|string|max:300']);

        return response()->json($this->procesar($datos['pregunta'], $request->user(), $asistencias));
    }

    /** Elige motor (Ollama → local) y devuelve respuesta + metadatos. */
    private function procesar(string $pregunta, $usuario, AsistenciaService $asistencias): array
    {
        $ollama = app(OllamaService::class);
        $texto = null;
        $motor = 'local';

        if ($ollama->disponible()) {
            $texto = $ollama->conversar($pregunta, $this->contexto($usuario, $pregunta));
            $motor = 'ollama:'.$ollama->modelo();
        }

        if ($texto === null) {
            $local = $this->responder(mb_strtolower($pregunta), $asistencias, $usuario);
            $texto = $local['titulo']."\n- ".implode("\n- ", $local['lineas']);
            $motor = $ollama->disponible() ? $motor : 'local (Ollama no disponible)';
        }

        return ['respuesta' => $texto, 'motor' => $motor, 'ollamaOk' => $ollama->disponible()];
    }

    /** Datos que el asistente puede usar, según el rol del usuario. */
    private function contexto($usuario, string $pregunta): string
    {
        $lineas = ['Fecha: '.now()->format('d/m/Y')];

        if ($usuario->rol === 'EMPLEADO') {
            $empleado = Empleado::find($usuario->empleado_id);
            if (! $empleado) {
                return "Usuario sin empleado vinculado.
";
            }
            $lineas[] = 'Consulta del EMPLEADO: '.$empleado->nombre_completo.' ('.$empleado->codigo.').'
                .' Sólo puedes hablar de sus datos, no de otros empleados.';
            $lineas[] = $this->fichaEmpleado($empleado);
        } elseif ($usuario->rol === 'SUPERVISOR') {
            $lineas[] = 'Consulta del SUPERVISOR: resumen agregado de su equipo y solicitudes pendientes. Sin sueldos ni nómina.';
            $lineas[] = $this->resumenEquipo($usuario);
        } else {
            if ($usuario->rol === 'RRHH') {
                $lineas[] = 'Consulta de RR.HH.: resumen general autorizado para su función.';
                $lineas[] = $this->resumenGeneral();
            } else {
                // FASE 2.E: ADMIN no recibe automáticamente registros laborales ni salarios.
                $lineas[] = 'Consulta de ADMIN: módulos técnicos. Sin expedientes, asistencia ni remuneraciones.';
            }
        }

        return implode("\n", $lineas)."\n";
    }

    private function fichaEmpleado(Empleado $empleado): string
    {
        // FASE 2.E: la ficha nunca incluye salario ni neto de planilla.
        $ultimo = Asistencia::where('empleado_id', $empleado->id)->orderByDesc('fecha')->limit(10)->get();
        $vac = \App\Models\Asistencias\SolicitudVacaciones::where('empleado_id', $empleado->id)
            ->orderByDesc('id')->limit(3)->get();

        $partes = [
            'Departamento: '.($empleado->departamento->nombre ?? '—'),
            'Cargo: '.($empleado->designacion->nombre ?? '—'),
        ];
        foreach ($ultimo as $a) {
            $partes[] = 'Asistencia '.$a->fecha->format('d/m').': '.$a->estado
                .($a->tardanza_min ? ' ('.$a->tardanza_min.' min tardanza)' : '');
        }
        foreach ($vac as $v) {
            $partes[] = 'Vacaciones '.$v->desde->format('d/m').' a '.$v->hasta->format('d/m').': '.$v->estado;
        }

        return implode("\n", $partes);
    }

    private function resumenGeneral(): string
    {
        $svc = app(AsistenciaService::class);
        $resumen = $svc->resumenPeriodo();
        $periodo = PeriodoPlanilla::orderByDesc('anio')->orderByDesc('mes')->first();
        $pendientes = \App\Models\Asistencias\Justificacion::where('estado', 'Pendiente')->count()
            + \App\Models\Asistencias\SolicitudVacaciones::where('estado', 'Pendiente')->count()
            + \App\Models\Asistencias\SolicitudPermiso::where('estado', 'Pendiente')->count();

        $partes = [
            'Empleados: '.Empleado::count().' (activos: '.Empleado::where('estado', 'Activo')->count().')',
            'Mes actual: '.$resumen['asistio'].' asistió, '.$resumen['tardanza'].' tardanzas ('
                .$resumen['minutos'].' min), '.$resumen['ausente'].' ausencias',
            'Puntualidad: '.$svc->puntualidad($resumen).' %',
            'Solicitudes pendientes: '.$pendientes,
        ];
        return implode("\n", $partes);
    }

    /** FASE 2.E: resumen agregado del equipo del supervisor, sin nómina ni salarios. */
    private function resumenEquipo($usuario): string
    {
        $mi = Empleado::find($usuario->empleado_id);
        $ids = $mi?->departamento_id
            ? Empleado::where('departamento_id', $mi->departamento_id)->where('estado', 'Activo')->pluck('id')
            : collect();

        if ($ids->isEmpty()) {
            return 'Sin equipo asignado aún. Solicite al Administrador la vinculación de su expediente al departamento.';
        }

        $filas = Asistencia::whereIn('empleado_id', $ids)
            ->whereMonth('fecha', now()->month)->whereYear('fecha', now()->year)
            ->selectRaw('estado, count(*) total')->groupBy('estado')->pluck('total', 'estado');

        $pendientes = \App\Models\Asistencias\Justificacion::whereIn('empleado_id', $ids)->where('estado', 'Pendiente')->count()
            + \App\Models\Asistencias\SolicitudVacaciones::whereIn('empleado_id', $ids)->where('estado', 'Pendiente')->count()
            + \App\Models\Asistencias\SolicitudPermiso::whereIn('empleado_id', $ids)->where('estado', 'Pendiente')->count();

        return implode("\n", [
            'Miembros activos del equipo: '.$ids->count(),
            'Este mes (agregado del equipo): '.($filas['Asistió'] ?? 0).' asistió, '
                .($filas['Tardanza'] ?? 0).' tardanzas, '.($filas['Ausente'] ?? 0).' ausencias.',
            'Solicitudes pendientes del equipo: '.$pendientes,
        ]);
    }

    private function buscarEmpleado(string $pregunta): ?Empleado
    {
        $q = Empleado::query();
        $q->where(fn ($w) => $w->whereRaw('LOWER(nombre_completo) like ?', ['%'.mb_strtolower($pregunta).'%'])
            ->orWhereRaw('LOWER(codigo) like ?', ['%'.mb_strtolower($pregunta).'%']));
        $emp = $q->first();
        if ($emp) {
            return $emp;
        }
        $palabras = collect(preg_split('/\s+/', $pregunta))->filter(fn ($p) => mb_strlen($p) > 3);
        if ($palabras->isEmpty()) {
            return null;
        }

        return Empleado::query()->where(function ($w) use ($palabras) {
            foreach ($palabras as $palabra) {
                $w->orWhereRaw('LOWER(nombre_completo) like ?', ['%'.mb_strtolower($palabra).'%'])
                  ->orWhereRaw('LOWER(apellido) like ?', ['%'.mb_strtolower($palabra).'%']);
            }
        })->first();
    }

    /** Motor local de respuestas (fallback cuando Ollama no responde). */
    private function responder(string $texto, AsistenciaService $asistencias, $usuario): array
    {
        // FASE 2.E: el motor local respeta el mismo alcance que Ollama.
        $empleado = $usuario->rol === 'EMPLEADO'
            ? Empleado::find($usuario->empleado_id)
            : null;

        if (str_contains($texto, 'planilla') || str_contains($texto, 'sueldo') || str_contains($texto, 'pago')) {
            // FASE 2.E: cada rol sólo ve sus propios datos de nómina.
            if ($usuario->rol === 'EMPLEADO') {
                $propia = \App\Models\Nomina\DetallePlanilla::with('periodo')
                    ->where('empleado_id', optional(Empleado::find($usuario->empleado_id))->id)
                    ->orderByDesc('id')->first();
                if ($propia && $propia->periodo?->estado !== 'Borrador') {
                    return ['titulo' => 'Su última boleta publicada ('.$propia->periodo->periodo.')', 'lineas' => [
                        'Neto a pagar: S/ '.number_format((float) $propia->neto, 2),
                        'Descárguela desde «Mi espacio → Mis boletas de pago».',
                    ]];
                }
            }

            return ['titulo' => 'Información de nómina', 'lineas' => [
                'Las boletas son personales: el empleado consulta las suyas en «Mi espacio → Mis boletas de pago».',
                'La planilla general se gestiona sólo en el módulo autorizado de RR. HH.',
            ]];
        }

        if ($empleado) {
            $resumen = Asistencia::where('empleado_id', $empleado->id)
                ->whereMonth('fecha', now()->month)->whereYear('fecha', now()->year)
                ->selectRaw("estado, count(*) total")->groupBy('estado')->pluck('total', 'estado');

            return ['titulo' => 'Ficha de '.$empleado->nombre_completo.' ('.$empleado->codigo.')', 'lineas' => [
                'Departamento: '.($empleado->departamento->nombre ?? '—').' · Cargo: '.($empleado->designacion->nombre ?? '—'),
                'Este mes: '.($resumen['Asistió'] ?? 0).' asistió, '.($resumen['Tardanza'] ?? 0).' tardanzas, '.($resumen['Ausente'] ?? 0).' ausencias',
            ]];
        }

        $resumen = $asistencias->resumenPeriodo();

        return ['titulo' => 'Resumen del mes', 'lineas' => [
            'Asistió: '.$resumen['asistio'].' · Tardanzas: '.$resumen['tardanza'].' · Ausencias: '.$resumen['ausente'],
            'Puntualidad: '.$asistencias->puntualidad($resumen).' %',
            'Para consultar una persona indique su nombre o código (ej. «Angela Cruz» o «HR-EMP-00001»).',
        ]];
    }
}
