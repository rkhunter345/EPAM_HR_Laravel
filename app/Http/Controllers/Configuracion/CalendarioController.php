<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\ModuloCrudController;
use App\Models\Configuracion\CalendarioTrabajo;

class CalendarioController extends ModuloCrudController
{
    protected string $modelo = CalendarioTrabajo::class;
    protected string $vista = 'configuracion.calendarios';
    protected string $ruta = 'calendarios';
    protected string $titulo = 'Calendario de trabajo';
    protected string $singular = 'calendario';
    protected string $descripcion = 'Horarios, tolerancia de entrada y duración del refrigerio.';
    protected array $columnas = [
        ['campo' => 'nombre', 'label' => 'Calendario'],
        ['campo' => 'dias_laborables', 'label' => 'Días'],
        ['campo' => 'hora_entrada', 'label' => 'Entrada'],
        ['campo' => 'hora_salida', 'label' => 'Salida'],
        ['campo' => 'tolerancia_min', 'label' => 'Tolerancia (min)'],
        ['campo' => 'refrigerio_min', 'label' => 'Refrigerio (min)'],
    ];
    protected array $campos = [
        ['name' => 'nombre', 'label' => 'Nombre del calendario', 'type' => 'text', 'col' => 6, 'req' => true],
        ['name' => 'dias_laborables', 'label' => 'Días laborables', 'type' => 'text', 'col' => 6],
        ['name' => 'hora_entrada', 'label' => 'Hora de entrada', 'type' => 'time', 'col' => 4],
        ['name' => 'hora_salida', 'label' => 'Hora de salida', 'type' => 'time', 'col' => 4],
        ['name' => 'tolerancia_min', 'label' => 'Tolerancia (minutos)', 'type' => 'number', 'col' => 4],
        ['name' => 'refrigerio_min', 'label' => 'Refrigerio (minutos)', 'type' => 'number', 'col' => 4],
    ];
    protected array $reglas = ['nombre' => 'required|string|max:80', 'dias_laborables' => 'nullable|string|max:60',
        'hora_entrada' => 'nullable', 'hora_salida' => 'nullable',
        'tolerancia_min' => 'nullable|integer|min:0|max:60', 'refrigerio_min' => 'nullable|integer|min:0|max:120'];
}
