<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Controlador base de los módulos maestros (compañía, sucursal, departamento...).
 * Cada módulo concreto sólo declara: modelo, vistas, ruta, título, columnas y campos.
 */
abstract class ModuloCrudController extends Controller
{
    /** @var class-string<Model> */
    protected string $modelo;

    /** carpeta de vistas, p.ej. 'organizacion.companias' */
    protected string $vista;

    /** prefijo de ruta, p.ej. 'companias' */
    protected string $ruta;

    protected string $titulo = 'Registro';

    protected string $singular = 'registro';

    protected string $descripcion = '';

    protected string $orden = 'nombre';

    /** Columnas del listado: [['campo' => 'nombre', 'label' => 'Nombre'], ...] */
    protected array $columnas = [];

    /** Campos del formulario */
    protected array $campos = [];

    protected array $reglas = [];

    /** Campos por los que se busca en el index */
    protected array $buscarEn = ['nombre'];

    protected function opciones(): array
    {
        return [];
    }

    protected function datosVista(): array
    {
        return [
            'titulo' => $this->titulo,
            'singular' => $this->singular,
            'descripcion' => $this->descripcion,
            'ruta' => $this->ruta,
            'columnas' => $this->columnas,
            'campos' => $this->campos,
            'opciones' => $this->opciones(),
            'vistaBase' => $this->vista,
        ];
    }

    public function index(Request $request)
    {
        $q = ($this->modelo)::query();

        if ($buscar = trim((string) $request->query('q'))) {
            $q->where(function ($sub) use ($buscar) {
                foreach ($this->buscarEn as $campo) {
                    $sub->orWhere($campo, 'like', "%{$buscar}%");
                }
            });
        }

        $registros = $q->orderBy($this->orden)->paginate(20)->withQueryString();

        return view($this->vista.'.index', $this->datosVista() + compact('registros', 'buscar'));
    }

    public function create()
    {
        return view($this->vista.'.create', $this->datosVista() + ['registro' => new $this->modelo]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas);
        $registro = ($this->modelo)::create($datos);

        return redirect()->route($this->ruta.'.show', $registro)
            ->with('ok', "{$this->titulo} registrado correctamente.");
    }

    public function show($id)
    {
        $registro = ($this->modelo)::findOrFail($id);

        return view($this->vista.'.show', $this->datosVista() + compact('registro'));
    }

    public function edit($id)
    {
        $registro = ($this->modelo)::findOrFail($id);

        return view($this->vista.'.edit', $this->datosVista() + compact('registro'));
    }

    public function update(Request $request, $id)
    {
        $registro = ($this->modelo)::findOrFail($id);
        $registro->update($request->validate($this->reglas));

        return redirect()->route($this->ruta.'.show', $registro)
            ->with('ok', "{$this->titulo} actualizado correctamente.");
    }

    public function destroy($id)
    {
        ($this->modelo)::findOrFail($id)->delete();

        return redirect()->route($this->ruta.'.index')
            ->with('ok', "{$this->titulo} eliminado.");
    }
}
