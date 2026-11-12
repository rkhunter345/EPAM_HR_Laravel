<?php

namespace App\Models\Asistencias;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalidaTemporal extends Model
{
    protected $table = 'salidas_temporales';

    protected $fillable = [
        'empleado_id', 'motivo', 'observacion', 'documento',
        'hora_salida', 'hora_retorno', 'estado', 'tolerancia_min',
    ];

    protected $casts = [
        'hora_salida' => 'datetime',
        'hora_retorno' => 'datetime',
        'tolerancia_min' => 'integer',
    ];

    public const MOTIVOS = [
        'Necesidad personal.',
        'Urgencia personal.',
        'Llamada urgente.',
        'Atención médica.',
        'Emergencia familiar.',
        'Coordinación laboral.',
        'Falla técnica.',
        'Otro.',
    ];

    public const ESTADO_ACTIVA = 'Monitoreo pausado';
    public const ESTADO_COMPLETADA = 'Completada';

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Empleados\Empleado::class);
    }
}
