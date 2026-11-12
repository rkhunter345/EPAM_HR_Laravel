<?php

namespace App\Http\Controllers\Organizacion;

use App\Http\Controllers\ModuloCrudController;
use App\Models\Organizacion\GradoEmpleado;

class GradoController extends ModuloCrudController
{
    protected string $modelo = GradoEmpleado::class;
    protected string $vista = 'organizacion.grados';
    protected string $ruta = 'grados';
    protected string $titulo = 'Grado del empleado';
    protected string $singular = 'grado';
    protected string $descripcion = 'Niveles jerárquicos usados en la evaluación del personal.';
    protected array $columnas = [
        ['campo' => 'nombre', 'label' => 'Grado'],
        ['campo' => 'nivel', 'label' => 'Nivel'],
        ['campo' => 'descripcion', 'label' => 'Descripción'],
    ];
    protected array $campos = [
        ['name' => 'nombre', 'label' => 'Nombre del grado', 'type' => 'text', 'col' => 6, 'req' => true],
        ['name' => 'nivel', 'label' => 'Nivel (número)', 'type' => 'number', 'col' => 6],
        ['name' => 'descripcion', 'label' => 'Descripción', 'type' => 'textarea', 'col' => 12],
    ];
    protected array $reglas = ['nombre' => 'required|string|max:80', 'nivel' => 'nullable|integer|min:1',
        'descripcion' => 'nullable|string|max:400'];
}
