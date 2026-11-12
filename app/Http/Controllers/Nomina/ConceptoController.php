<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\ModuloCrudController;
use App\Models\Nomina\ConceptoSalarial;

class ConceptoController extends ModuloCrudController
{
    protected string $modelo = ConceptoSalarial::class;
    protected string $vista = 'planilla.conceptos';
    protected string $ruta = 'conceptos';
    protected string $titulo = 'Concepto salarial';
    protected string $singular = 'concepto';
    protected string $descripcion = 'Ingresos, descuentos, aportes e impuestos aplicados en la planilla.';
    protected array $buscarEn = ['nombre', 'codigo'];
    protected array $columnas = [
        ['campo' => 'codigo', 'label' => 'Código'],
        ['campo' => 'nombre', 'label' => 'Concepto'],
        ['campo' => 'tipo', 'label' => 'Tipo', 'badge' => true],
        ['campo' => 'monto', 'label' => 'Monto fijo'],
        ['campo' => 'porcentaje', 'label' => 'Porcentaje'],
    ];
    protected array $campos = [
        ['name' => 'codigo', 'label' => 'Código', 'type' => 'text', 'col' => 4, 'req' => true],
        ['name' => 'nombre', 'label' => 'Nombre del concepto', 'type' => 'text', 'col' => 8, 'req' => true],
        ['name' => 'tipo', 'label' => 'Tipo', 'type' => 'select', 'col' => 4, 'opciones' => ['Ingreso' => 'Ingreso', 'Descuento' => 'Descuento', 'Aporte' => 'Aporte', 'Impuesto' => 'Impuesto', 'Aporte empleador' => 'Aporte empleador']],
        ['name' => 'monto', 'label' => 'Monto fijo (S/)', 'type' => 'number', 'col' => 4],
        ['name' => 'porcentaje', 'label' => 'Porcentaje (%)', 'type' => 'number', 'col' => 4],
        ['name' => 'descripcion', 'label' => 'Descripción', 'type' => 'textarea', 'col' => 12],
        ['name' => 'activo', 'label' => 'Activo', 'type' => 'checkbox', 'col' => 12],
    ];
    protected array $reglas = ['codigo' => 'required|string|max:30', 'nombre' => 'required|string|max:120',
        'tipo' => 'required|string|max:30', 'monto' => 'nullable|numeric|min:0',
        'porcentaje' => 'nullable|numeric|min:0|max:100', 'descripcion' => 'nullable|string|max:400', 'activo' => 'boolean'];
}
