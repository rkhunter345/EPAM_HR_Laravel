<?php

namespace App\Models\Asistencias;

use App\Models\Empleados\Empleado;
use App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Justificacion extends Model
{
    use HasFactory;

    protected $table = 'justificaciones';

    protected $fillable = [
        'empleado_id', 'fecha', 'motivo', 'horario_afectado', 'documento',
        'estado', 'revisado_por', 'comunicante', 'explicacion', 'observacion',
    ];

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }

    public const ESTADOS = ['Pendiente', 'Aprobada', 'Rechazada', 'Información solicitada'];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por', 'id');
    }
}
