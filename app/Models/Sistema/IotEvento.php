<?php

namespace App\Models\Sistema;

use App\Models\Empleados\Empleado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IotEvento extends Model
{
    protected $table = 'iot_eventos';
    protected $fillable = ['estacion_id', 'empleado_id', 'tipo', 'detectado_en', 'duracion_min',
        'evidencia', 'estado', 'observacion'];
    protected $casts = ['detectado_en' => 'datetime'];

    public const TIPOS = ['ausencia_puesto', 'uso_celular', 'regreso', 'marcacion_qr'];
    public const ESTADOS = ['Registrado', 'Revisado', 'Descartado'];

    public function estacion(): BelongsTo
    {
        return $this->belongsTo(IotEstacion::class, 'estacion_id');
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }
}
