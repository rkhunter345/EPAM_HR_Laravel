<?php

namespace App\Http\Controllers\Organizacion;

use App\Http\Controllers\ModuloCrudController;
use App\Models\Organizacion\GrupoEmpleados;

class GrupoController extends ModuloCrudController
{
    protected string $modelo = GrupoEmpleados::class;
    protected string $vista = 'organizacion.grupos';
    protected string $ruta = 'grupos';
    protected string $titulo = 'Grupo de empleados';
    protected string $singular = 'grupo';
    protected string $descripcion = 'Agrupaciones para reportes y turnos.';
    protected array $columnas = [
        ['campo' => 'nombre', 'label' => 'Grupo'],
        ['campo' => 'descripcion', 'label' => 'Descripción'],
    ];
    protected array $campos = [
        ['name' => 'nombre', 'label' => 'Nombre del grupo', 'type' => 'text', 'col' => 6, 'req' => true],
        ['name' => 'descripcion', 'label' => 'Descripción', 'type' => 'textarea', 'col' => 12],
    ];
    protected array $reglas = ['nombre' => 'required|string|max:80', 'descripcion' => 'nullable|string|max:400'];
}
