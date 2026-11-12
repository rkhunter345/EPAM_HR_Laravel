<?php

namespace App\Http\Controllers\Organizacion;

use App\Http\Controllers\ModuloCrudController;
use App\Models\Organizacion\Compania;
use App\Models\Organizacion\Sucursal;

class SucursalController extends ModuloCrudController
{
    protected string $modelo = Sucursal::class;
    protected string $vista = 'organizacion.sucursales';
    protected string $ruta = 'sucursales';
    protected string $titulo = 'Sucursal';
    protected string $singular = 'sucursal';
    protected string $descripcion = 'Locales o sedes donde presta servicio la empresa.';
    protected array $columnas = [
        ['campo' => 'nombre', 'label' => 'Sucursal'],
        ['campo' => 'compania.nombre', 'label' => 'Compañía'],
        ['campo' => 'ciudad', 'label' => 'Ciudad'],
        ['campo' => 'responsable', 'label' => 'Responsable'],
    ];
    protected array $campos = [
        ['name' => 'compania_id', 'label' => 'Compañía', 'type' => 'select_dinamico', 'col' => 6, 'req' => true, 'fuente' => 'companias'],
        ['name' => 'nombre', 'label' => 'Nombre de la sucursal', 'type' => 'text', 'col' => 6, 'req' => true],
        ['name' => 'ciudad', 'label' => 'Ciudad', 'type' => 'text', 'col' => 6],
        ['name' => 'direccion', 'label' => 'Dirección', 'type' => 'text', 'col' => 6],
        ['name' => 'responsable', 'label' => 'Responsable', 'type' => 'text', 'col' => 6],
        ['name' => 'activo', 'label' => 'Activo', 'type' => 'checkbox', 'col' => 6],
    ];
    protected array $reglas = ['compania_id' => 'required|exists:companias,id', 'nombre' => 'required|string|max:120',
        'ciudad' => 'nullable|string|max:80', 'direccion' => 'nullable|string|max:200',
        'responsable' => 'nullable|string|max:120', 'activo' => 'boolean'];

    protected function opciones(): array
    {
        return ['companias' => Compania::orderBy('nombre')->pluck('nombre', 'id')];
    }
}
