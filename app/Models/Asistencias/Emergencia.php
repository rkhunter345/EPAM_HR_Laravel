<?php

namespace App\Models\Asistencias;

use App\Models\Empleados\Empleado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Emergencia extends Model
{
    protected $table = 'emergencias';

    protected $fillable = [
        'empleado_id', 'reportado_por_usuario', 'reportado_por', 'motivo', 'fecha_hora',
        'lugar', 'observacion', 'adjunto', 'estado', 'revisado_por', 'revisado_en',
    ];

    protected $casts = ['fecha_hora' => 'datetime', 'revisado_en' => 'datetime'];

    public const ESTADOS = ['Pendiente', 'En revisión', 'Aprobada', 'Rechazada', 'Derivada'];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }
}
