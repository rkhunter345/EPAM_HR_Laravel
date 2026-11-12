<?php

namespace App\Http\Controllers\Organizacion;

use App\Http\Controllers\ModuloCrudController;
use App\Models\Organizacion\Compania;

class CompaniaController extends ModuloCrudController
{
    protected string $modelo = Compania::class;
    protected string $vista = 'organizacion.companias';
    protected string $ruta = 'companias';
    protected string $titulo = 'Compañía';
    protected string $singular = 'compañía';
    protected string $descripcion = 'Datos legales y de contacto de la empresa.';
    protected array $columnas = [
        ['campo' => 'nombre', 'label' => 'Nombre'],
        ['campo' => 'ruc', 'label' => 'RUC'],
        ['campo' => 'telefono', 'label' => 'Teléfono'],
        ['campo' => 'direccion', 'label' => 'Dirección'],
    ];
    protected array $campos = [
        ['name' => 'nombre', 'label' => 'Nombre de la compañía', 'type' => 'text', 'col' => 6, 'req' => true],
        ['name' => 'ruc', 'label' => 'RUC', 'type' => 'text', 'col' => 6],
        ['name' => 'moneda', 'label' => 'Moneda', 'type' => 'select', 'col' => 6, 'opciones' => ['PEN' => 'PEN - Sol', 'USD' => 'USD - Dólar']],
        ['name' => 'telefono', 'label' => 'Teléfono', 'type' => 'text', 'col' => 6],
        ['name' => 'correo', 'label' => 'Correo', 'type' => 'email', 'col' => 6],
        ['name' => 'direccion', 'label' => 'Dirección', 'type' => 'text', 'col' => 6],
        ['name' => 'activo', 'label' => 'Activo', 'type' => 'checkbox', 'col' => 12],
    ];
    protected array $reglas = ['nombre' => 'required|string|max:160', 'ruc' => 'nullable|string|max:20',
        'moneda' => 'nullable|string|max:10', 'telefono' => 'nullable|string|max:30',
        'correo' => 'nullable|email|max:160', 'direccion' => 'nullable|string|max:200', 'activo' => 'boolean'];
}
