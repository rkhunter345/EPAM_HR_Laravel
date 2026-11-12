<?php

namespace App\Http\Controllers\Organizacion;

use App\Http\Controllers\ModuloCrudController;
use App\Models\Organizacion\Departamento;
use App\Models\Organizacion\Designacion;

class DesignacionController extends ModuloCrudController
{
    protected string $modelo = Designacion::class;
    protected string $vista = 'organizacion.designaciones';
    protected string $ruta = 'designaciones';
    protected string $titulo = 'Designación';
    protected string $singular = 'designación';
    protected string $descripcion = 'Puestos de trabajo asociados a cada departamento.';
    protected array $columnas = [
        ['campo' => 'nombre', 'label' => 'Designación'],
        ['campo' => 'departamento.nombre', 'label' => 'Departamento'],
        ['campo' => 'descripcion', 'label' => 'Descripción'],
    ];
    protected array $campos = [
        ['name' => 'departamento_id', 'label' => 'Departamento', 'type' => 'select_dinamico', 'col' => 6, 'req' => true, 'fuente' => 'departamentos'],
        ['name' => 'nombre', 'label' => 'Nombre de la designación', 'type' => 'text', 'col' => 6, 'req' => true],
        ['name' => 'descripcion', 'label' => 'Descripción', 'type' => 'textarea', 'col' => 12],
        ['name' => 'activo', 'label' => 'Activo', 'type' => 'checkbox', 'col' => 12],
    ];
    protected array $reglas = ['departamento_id' => 'required|exists:departamentos,id', 'nombre' => 'required|string|max:120',
        'descripcion' => 'nullable|string|max:400', 'activo' => 'boolean'];

    protected function opciones(): array
    {
        return ['departamentos' => Departamento::orderBy('nombre')->pluck('nombre', 'id')];
    }
}
