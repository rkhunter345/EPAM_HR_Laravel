<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\ModuloCrudController;
use App\Models\Configuracion\TipoContrato;

class TipoContratoController extends ModuloCrudController
{
    protected string $modelo = TipoContrato::class;
    protected string $vista = 'configuracion.tipos-contrato';
    protected string $ruta = 'tipos-contrato';
    protected string $titulo = 'Tipo de contrato';
    protected string $singular = 'tipo de contrato';
    protected string $descripcion = 'Modalidades contractuales y su duración.';
    protected array $columnas = [
        ['campo' => 'nombre', 'label' => 'Tipo de contrato'],
        ['campo' => 'duracion_meses', 'label' => 'Duración (meses)'],
        ['campo' => 'descripcion', 'label' => 'Descripción'],
    ];
    protected array $campos = [
        ['name' => 'nombre', 'label' => 'Nombre del contrato', 'type' => 'text', 'col' => 6, 'req' => true],
        ['name' => 'duracion_meses', 'label' => 'Duración en meses', 'type' => 'number', 'col' => 6],
        ['name' => 'descripcion', 'label' => 'Descripción', 'type' => 'textarea', 'col' => 12],
    ];
    protected array $reglas = ['nombre' => 'required|string|max:80', 'duracion_meses' => 'nullable|integer|min:1',
        'descripcion' => 'nullable|string|max:400'];
}
