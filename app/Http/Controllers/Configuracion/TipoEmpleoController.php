<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\ModuloCrudController;
use App\Models\Configuracion\TipoEmpleo;

class TipoEmpleoController extends ModuloCrudController
{
    protected string $modelo = TipoEmpleo::class;
    protected string $vista = 'configuracion.tipos-empleo';
    protected string $ruta = 'tipos-empleo';
    protected string $titulo = 'Tipo de empleo';
    protected string $singular = 'tipo de empleo';
    protected string $descripcion = 'Clasificación laboral: tiempo completo, medio tiempo, locación de servicios.';
    protected array $columnas = [
        ['campo' => 'nombre', 'label' => 'Tipo de empleo'],
        ['campo' => 'descripcion', 'label' => 'Descripción'],
    ];
    protected array $campos = [
        ['name' => 'nombre', 'label' => 'Nombre', 'type' => 'text', 'col' => 6, 'req' => true],
        ['name' => 'descripcion', 'label' => 'Descripción', 'type' => 'textarea', 'col' => 12],
    ];
    protected array $reglas = ['nombre' => 'required|string|max:80', 'descripcion' => 'nullable|string|max:400'];
}
