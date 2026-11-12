<?php

namespace App\Http\Controllers\Organizacion;

use App\Http\Controllers\ModuloCrudController;
use App\Models\Organizacion\Departamento;
use App\Models\Organizacion\Sucursal;

class DepartamentoController extends ModuloCrudController
{
    protected string $modelo = Departamento::class;
    protected string $vista = 'organizacion.departamentos';
    protected string $ruta = 'departamentos';
    protected string $titulo = 'Departamento';
    protected string $singular = 'departamento';
    protected string $descripcion = 'Áreas funcionales de la organización.';
    protected array $columnas = [
        ['campo' => 'nombre', 'label' => 'Departamento'],
        ['campo' => 'sucursal.nombre', 'label' => 'Sucursal'],
        ['campo' => 'responsable', 'label' => 'Responsable'],
        ['campo' => 'descripcion', 'label' => 'Descripción'],
    ];
    protected array $campos = [
        ['name' => 'sucursal_id', 'label' => 'Sucursal', 'type' => 'select_dinamico', 'col' => 6, 'req' => true, 'fuente' => 'sucursales'],
        ['name' => 'nombre', 'label' => 'Nombre del departamento', 'type' => 'text', 'col' => 6, 'req' => true],
        ['name' => 'responsable', 'label' => 'Responsable', 'type' => 'text', 'col' => 6],
        ['name' => 'activo', 'label' => 'Activo', 'type' => 'checkbox', 'col' => 6],
        ['name' => 'descripcion', 'label' => 'Descripción', 'type' => 'textarea', 'col' => 12],
    ];
    protected array $reglas = ['sucursal_id' => 'required|exists:sucursales,id', 'nombre' => 'required|string|max:120',
        'responsable' => 'nullable|string|max:120', 'descripcion' => 'nullable|string|max:400', 'activo' => 'boolean'];

    protected function opciones(): array
    {
        return ['sucursales' => Sucursal::orderBy('nombre')->pluck('nombre', 'id')];
    }
}
